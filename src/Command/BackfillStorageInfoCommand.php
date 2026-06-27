<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

/**
 * bin/cake backfill_storage_info
 *
 * Backfills the per-server storage/software fields on existing submissions from
 * their stored JSON, using the SAME parser the application runs at submission
 * time (parse() and the parse_* methods below are copied verbatim from
 * SubmissionsController, so results are identical to a fresh submission).
 *
 * This is a one-time maintenance command, hence the duplicated parser code: it
 * intentionally does not touch the controller.
 *
 * Safety model:
 *   1. NULL-only  — a column is written ONLY if its current DB value is empty.
 *                   Correctly-parsed values are never overwritten.
 *   2. Content-aware — a value is written ONLY if the parser produced a non-empty
 *                   value for it.
 * Together these make the command idempotent and regression-free.
 *
 * Dry-run is the DEFAULT. Pass --commit to actually write.
 *   --id N            limit to one submission
 *   --show-conflicts  read-only: report columns whose DB value differs from the
 *                     value the parser produces (never writes)
 *
 * Runs against the 'default' datasource (the shadow DB per config/app_local.php).
 * Do NOT point it at production without explicit authorisation.
 */
class BackfillStorageInfoCommand extends Command
{
    public static function defaultName(): string
    {
        return 'backfill_storage_info';
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription(
            'Backfill per-server storage and software-version fields from each submission\'s ' .
            'stored JSON, using the application\'s own parser. NULL-only: never overwrites an ' .
            'existing value. Dry-run unless --commit is given.'
        );

        $parser->addOption('commit', [
            'boolean' => true,
            'default' => false,
            'help'    => 'Actually write changes. Without this the command is a dry run.',
        ]);

        $parser->addOption('id', [
            'help' => 'Limit to a single submission id (useful for verification).',
        ]);

        $parser->addOption('show-conflicts', [
            'boolean' => true,
            'default' => false,
            'help'    => 'Report columns whose existing DB value differs from the parsed value. ' .
                         'Read-only: never writes, regardless of --commit.',
        ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $commit = (bool)$args->getOption('commit');
        $onlyId = $args->getOption('id');
        $showConflicts = (bool)$args->getOption('show-conflicts');

        if ($showConflicts) {
            $io->out('<warning>Conflict report — read-only, no changes will be written.</warning>');
        } elseif (!$commit) {
            $io->out('<warning>Dry-run mode — no changes will be written. Pass --commit to write.</warning>');
        }

        /** @var \Cake\Database\Connection $db */
        $db = ConnectionManager::get('default');

        $sql = 'SELECT id, ' . implode(', ', $this->targetColumns()) . ' FROM submissions';
        $params = [];
        if ($onlyId !== null) {
            $sql .= ' WHERE id = ?';
            $params[] = (int)$onlyId;
        }
        $sql .= ' ORDER BY id ASC';

        $rows = $db->execute($sql, $params)->fetchAll('assoc');
        $io->out(count($rows) . ' submission(s) to inspect.');

        $changedRows = 0;
        $noJson = 0;
        $totalFields = 0;
        $conflicts = 0;

        foreach ($rows as $row) {
            $id = (int)$row['id'];

            $json = $this->loadJson($id);
            if ($json === null) {
                $noJson++;
                continue;
            }

            $parsed = $this->runParser($json);
            if ($parsed === null) {
                continue;
            }

            // Report-only mode: flag columns where the DB already holds a non-empty
            // value that differs from what the parser produces. Never writes.
            if ($showConflicts) {
                foreach ($this->targetColumns() as $col) {
                    $value = $parsed->{$col} ?? null;
                    if ($this->isEmpty($value) || $this->isEmpty($row[$col] ?? null)) {
                        continue;
                    }
                    if (trim((string)$value) !== trim((string)$row[$col])) {
                        $conflicts++;
                        $io->out(sprintf(
                            '  #%d  CONFLICT %s: db="%s" json="%s" (left unchanged)',
                            $id,
                            $col,
                            (string)$row[$col],
                            (string)$value
                        ));
                    }
                }
                continue;
            }

            // Keep only columns that are currently empty AND have a non-empty parsed value.
            $updates = [];
            foreach ($this->targetColumns() as $col) {
                $value = $parsed->{$col} ?? null;
                if (!$this->isEmpty($value) && $this->isEmpty($row[$col] ?? null)) {
                    $updates[$col] = (string)$value;
                }
            }

            if (!$updates) {
                continue;
            }

            $changedRows++;
            $totalFields += count($updates);

            foreach ($updates as $col => $value) {
                $io->verbose(sprintf('  #%d  %s: %s -> %s', $id, $col, $this->show($row[$col] ?? null), $this->show($value)));
            }

            if ($commit) {
                $this->updateRow($db, $id, $updates);
            }
        }

        if ($showConflicts) {
            $io->out(sprintf('%d conflict(s) found. %d had no readable JSON. Nothing was changed.', $conflicts, $noJson));

            return self::CODE_SUCCESS;
        }

        $io->out(sprintf(
            '%s %d field(s) across %d submission(s). %d had no readable JSON.',
            $commit ? 'Wrote' : 'Would write',
            $totalFields,
            $changedRows,
            $noJson
        ));

        if (!$commit) {
            $io->out('Dry-run complete — no changes written. Re-run with --commit to apply.');
        }

        return self::CODE_SUCCESS;
    }

    /** @return array<int,string> */
    private function targetColumns(): array
    {
        return [
            'information_ds_storage_type',
            'information_ds_storage_interface',
            'information_ds_software_version',
            'information_md_storage_type',
            'information_md_storage_interface',
            'information_md_software_version',
        ];
    }

    /**
     * Run the application's parser over a decoded JSON and return the populated
     * object, or null if parsing failed. Errors/notices from the parser (it
     * assumes a well-formed structure) are suppressed so a single odd submission
     * doesn't abort the batch.
     *
     * @param array<mixed> $json
     */
    private function runParser(array $json): ?object
    {
        $submission = new \stdClass();

        $prev = error_reporting(E_ERROR | E_PARSE);
        try {
            $submission = $this->parse($submission, $json);
        } catch (\Throwable $e) {
            return null;
        } finally {
            error_reporting($prev);
        }

        return $submission;
    }

    /**
     * Read and decode a submission's stored JSON (per-id file, as written by add()).
     *
     * @return array<mixed>|null
     */
    private function loadJson(int $id): ?array
    {
        $file = ROOT . DS . 'webroot' . DS . 'files' . DS . 'submissions' . DS . $id . '.json';
        if (!file_exists($file)) {
            return null;
        }

        $json = json_decode((string)file_get_contents($file), true);

        return is_array($json) ? $json : null;
    }

    /**
     * @param array<string,string> $updates
     */
    private function updateRow(\Cake\Database\Connection $db, int $id, array $updates): void
    {
        $set = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($updates)));
        $db->execute(
            "UPDATE submissions SET $set WHERE id = ?",
            array_merge(array_values($updates), [$id])
        );
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || trim((string)$value) === '';
    }

    private function show(mixed $value): string
    {
        return $this->isEmpty($value) ? '(empty)' : (string)$value;
    }

    // ------------------------------------------------------------------------
    // Parser — copied verbatim from SubmissionsController (one-time tool).
    // Keep in sync manually if the controller's parsing ever changes.
    // ------------------------------------------------------------------------

    private function parse($submission, $json)
    {
        $submission->errors = [];

        // Institution
        $json_site = $this->find_information($json, 'type', 'SITE');

        $submission->information_institution = $json_site['att']['institution'];

        // Supercomputer
        $json_supercomputer = $this->find_information($json_site, 'type', 'SUPERCOMPUTER');

        if (!$json_supercomputer) {
            $submission->errors[] = 'Your submission must contain information about at least one <strong>SUPERCOMPUTER</strong>';
        }

        $submission->information_system = $json_supercomputer['att']['name'] ?? null;

        // IO500
        $json_io500 = $this->find_information($json_site, 'type', 'IO500');

        if (!$json_io500) {
            $submission->errors[] = 'Your submission must contain information about the <strong>IO500</strong> execution';
        }

        $submission->information_client_nodes = $json_io500['att']['number_clientNodes'] ?? 0;
        $submission->information_client_procs_per_node = $json_io500['att']['procsPerNode'] ?? 0;
        $submission->information_client_total_procs = $submission->information_client_nodes * $submission->information_client_procs_per_node;

        $submission->information_server_nodes = $json_io500['att']['number_serverNodes'] ?? 0;

        if ($submission->information_client_nodes == 10) {
            $submission->information_10_node_challenge = true;
        }

        $submission->information_note = $json_io500['att']['note'] ?? 0;

        $submission->information_ds_storage_type = $json_io500['att']['storage type'] ?? null;
        $submission->information_filesystem_type = $json_io500['att']['type of filesystem'] ?? null;

        $submission->information_storage_capacity = isset($json_io500['att']['storage net capacity']) ? implode(' ', $json_io500['att']['storage net capacity']) : null;
        $submission->information_clients_interconnect_bandwidth = isset($json_io500['att']['clients network bandwidth']) ? implode(' ', $json_io500['att']['clients network bandwidth']) : null;
        $submission->information_servers_interconnect_bandwidth = isset($json_io500['att']['servers network bandwidth']) ? implode(' ', $json_io500['att']['servers network bandwidth']) : null;

        // Client Nodes
        $json_client = $this->find_information($json_supercomputer, 'type', 'NODES');

        if (!$json_client) {
            $submission->errors[] = 'Your submission must contain information about the compute <strong>NODES</strong>';
        } else {
            $submission->information_client_operating_system = $json_client['att']['distribution'] ?? null;
            $submission->information_client_operating_system_version = $json_client['att']['distribution version'] ?? null;
            $submission->information_client_kernel_version = $json_client['att']['kernel version'] ?? null;

            $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

            $json_client_processor = $this->find_information($json_client, 'type', 'PROCESSOR');

            if (!$json_client_processor) {
                $submission->errors[] = 'Your submission must contain information about the <strong>PROCESSOR</strong> in the compute <strong>NODES</strong>';
            } else {
                $submission->information_client_architecture = $json_client_processor['att']['architecture'] ?? null;
                $submission->information_client_model = $json_client_processor['att']['model'] ?? null;
                $submission->information_client_sockets = $json_client_processor['att']['sockets'] ?? null;
                $submission->information_client_cores_per_socket = $json_client_processor['att']['cores per socket'] ?? null;
                $submission->information_client_clock = isset($json_client_processor['att']['frequency']) ? implode(' ', $json_client_processor['att']['frequency']) : null;
            }

            $json_client_memory = $this->find_information($json_client, 'type', 'MEMORY');

            if (!$json_client_memory) {
                $submission->errors[] = 'Your submission must contain information about the <strong>MEMORY</strong> in the compute <strong>NODES</strong>';
            } else {
                $submission->information_client_volatile_memory_capacity = isset($json_client_memory['att']['net capacity']) ? implode(' ', $json_client_memory['att']['net capacity']) : null;
            }

            $json_client_interconnect = $this->find_information($json_client, 'type', 'INTERCONNECT');

            if (!$json_client_interconnect) {
                $submission->errors[] = 'Your submission must contain information about the <strong>INTERCONNECT</strong> in the compute <strong>NODES</strong>';
            } else {
                $submission->information_client_interconnect_type = $json_client_interconnect['att']['type'] ?? null;
                $submission->information_client_interconnect_vendor = $json_client_interconnect['att']['vendor'] ?? null;
                $submission->information_client_interconnect_bandwidth = isset($json_client_interconnect['att']['peak throughput']) ? implode(' ', $json_client_interconnect['att']['peak throughput']) : null;
                $submission->information_client_interconnect_links = $json_client_interconnect['att']['links'] ?? null;
                $submission->information_client_interconnect_rdma = isset($json_client_interconnect['att']['features']) ? (strpos($json_client_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
            }
        }

        $json_storage_system = $this->find_information($json, 'type', 'STORAGESYSTEM');

        if (!$json_storage_system) {
            $submission->errors[] = 'Your submission must contain information about the <strong>STORAGE SYSTEM</strong>';
        } else {
            $submission->information_filesystem_type = $json_storage_system['att']['software'] ?? null;
            $submission->information_filesystem_name = $json_storage_system['att']['name'] ?? null;
            $submission->information_filesystem_version = $json_storage_system['att']['version'] ?? null;

            $submission->information_storage_vendor = $json_storage_system['att']['vendor'] ?? null;

            $submission->information_production = (($json_storage_system['att']['usage'] ?? '') === 'production');

            $submission->information_client_spdk = isset($json_storage_system['att']['frameworks']) ? (strpos($json_storage_system['att']['frameworks'], 'SPDK') === false ? false : true) : false;
            $submission->information_client_dpdk = isset($json_storage_system['att']['frameworks']) ? (strpos($json_storage_system['att']['frameworks'], 'DPDK') === false ? false : true) : false;

            // LUSTRE
            $json_lustre = $this->find_information($json_storage_system, 'type', 'LUSTRE');

            if ($json_lustre) {
                $submission = $this->parse_lustre($submission, $json_lustre);
            }

            // SPECTRUMSCALE
            $json_spectrum = $this->find_information($json_storage_system, 'type', 'SPECTRUMSCALE');

            if ($json_spectrum) {
                $submission = $this->parse_spectrum($submission, $json_spectrum);
            }

            // BEEGFS
            $json_beegfs = $this->find_information($json_storage_system, 'type', 'BEEGFS');

            if ($json_beegfs) {
                $submission = $this->parse_beegfs($submission, $json_beegfs);
            }

            // NAS
            $json_nas = $this->find_information($json_storage_system, 'type', 'NAS');

            if ($json_nas) {
                $submission = $this->parse_nas($submission, $json_nas);
            }

            // OTHER
            $json_other = $this->find_information($json_storage_system, 'type', 'OTHER');

            if ($json_other) {
                $submission = $this->parse_other($submission, $json_other);
            }

            // DAOS
            $json_daos = $this->find_information($json_storage_system, 'type', 'DAOS');

            if ($json_daos) {
                $submission = $this->parse_daos($submission, $json_daos);
            }

            // WEKAIO
            $json_wekaio = $this->find_information($json_storage_system, 'type', 'WEKAIO');

            if ($json_wekaio) {
                $submission = $this->parse_daos($submission, $json_wekaio);
            }
        }

        return $submission;
    }

    private function parse_lustre($submission, $json_lustre)
    {
        $submission->information_ds_software_version = $json_lustre['att']['version'] ?? null;
        $submission->information_md_software_version = $json_lustre['att']['version'] ?? null;

        // Data Server
        $json_lustre_server = $this->find_information($json_lustre, 'type', 'OSS');

        $submission->information_ds_nodes = $json_lustre_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_lustre_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_lustre_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_lustre_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_lustre_server_processor = $this->find_information($json_lustre_server, 'type', 'PROCESSOR');

        if ($json_lustre_server_processor) {
            $submission->information_ds_architecture = $json_lustre_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_lustre_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_lustre_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_lustre_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_lustre_server_processor['att']['frequency']) ? implode(' ', $json_lustre_server_processor['att']['frequency']) : null;
        }

        $json_lustre_server_memory = $this->find_information($json_lustre_server, 'type', 'MEMORY');

        if ($json_lustre_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_lustre_server_memory['att']['net capacity']) ? implode(' ', $json_lustre_server_memory['att']['net capacity']) : null;
        }

        $json_lustre_server_interconnect = $this->find_information($json_lustre_server, 'type', 'INTERCONNECT');

        if ($json_lustre_server_interconnect) {
            $submission->information_ds_network = $json_lustre_server_media['att']['count'] ?? null; // equals to information_ds_interconnect_type
            $submission->information_ds_interconnect_type = $json_lustre_server_interconnect['att']['type'] ?? null;
            $submission->information_ds_interconnect_vendor = $json_lustre_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_lustre_server_interconnect['att']['peak throughput']) ? implode(' ', $json_lustre_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_lustre_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_lustre_server_interconnect['att']['features']) ? (strpos($json_lustre_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
        }

        $json_lustre_server_media = $this->find_information($json_lustre_server, 'type', 'STORAGEMEDIA');

        if ($json_lustre_server_media) {
            $submission->information_ds_storage_type = $json_lustre_server_media['att']['type'] ?? null;
            $submission->information_ds_storage_interface = $json_lustre_server_media['att']['interface'] ?? null;
        }

        // Metadata Server
        $json_lustre_server = $this->find_information($json_lustre, 'type', 'MDS');

        $submission->information_md_nodes = $json_lustre_server['att']['count'] ?? null;
        $submission->information_md_operating_system = $json_lustre_server['att']['distribution'] ?? null;
        $submission->information_md_operating_system_version = $json_lustre_server['att']['distribution version'] ?? null;
        $submission->information_md_kernel_version = $json_lustre_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_lustre_server_processor = $this->find_information($json_lustre_server, 'type', 'PROCESSOR');

        if ($json_lustre_server_processor) {
            $submission->information_md_architecture = $json_lustre_server_processor['att']['architecture'] ?? null;
            $submission->information_md_model = $json_lustre_server_processor['att']['model'] ?? null;
            $submission->information_md_sockets = $json_lustre_server_processor['att']['sockets'] ?? null;
            $submission->information_md_cores_per_socket = $json_lustre_server_processor['att']['cores per socket'] ?? null;
            $submission->information_md_clock = isset($json_lustre_server_processor['att']['frequency']) ? implode(' ', $json_lustre_server_processor['att']['frequency']) : null;
        }

        $json_lustre_server_memory = $this->find_information($json_lustre_server, 'type', 'MEMORY');

        if ($json_lustre_server_memory) {
            $submission->information_md_volatile_memory_capacity = isset($json_lustre_server_memory['att']['net capacity']) ? implode(' ', $json_lustre_server_memory['att']['net capacity']) : null;
        }

        $json_lustre_server_interconnect = $this->find_information($json_lustre_server, 'type', 'INTERCONNECT');

        if ($json_lustre_server_interconnect) {
            $submission->information_md_interconnect_type = $json_lustre_server_interconnect['att']['type'] ?? null; // same as information_md_network
            $submission->information_md_interconnect_vendor = $json_lustre_server_interconnect['att']['vendor'] ?? null;
            $submission->information_md_interconnect_bandwidth = isset($json_lustre_server_interconnect['att']['peak throughput']) ? implode(' ', $json_lustre_server_interconnect['att']['peak throughput']) : null;
            $submission->information_md_interconnect_links = $json_lustre_server_interconnect['att']['links'] ?? null;
            $submission->information_md_interconnect_rdma = isset($json_lustre_server_interconnect['att']['features']) ? (strpos($json_lustre_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_md_network = $submission->information_md_interconnect_type;
        }

        $json_lustre_server_media = $this->find_information($json_lustre_server, 'type', 'STORAGEMEDIA', 1);

        if ($json_lustre_server_media) {
            $submission->information_md_media_primary_type = $json_lustre_server_media['att']['type'] ?? null; // same as information_md_storage_type
            $submission->information_md_media_primary_vendor = $json_lustre_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_primary_interface = $json_lustre_server_media['att']['interface'] ?? null; // same as information_md_storage_interface
            $submission->information_md_media_primary_count = $json_lustre_server_media['att']['count'] ?? null;
            $submission->information_md_media_primary_capacity = isset($json_lustre_server_media['att']['net capacity']) ? implode(' ', $json_lustre_server_media['att']['net capacity']) : null;

            $submission->information_md_storage_type = $submission->information_md_media_primary_type;
            $submission->information_md_storage_interface = $submission->information_md_media_primary_interface;
        }

        $json_lustre_server_media = $this->find_information($json_lustre_server, 'type', 'STORAGEMEDIA', 2);

        if ($json_lustre_server_media) {
            $submission->information_md_media_secondary_type = $json_lustre_server_media['att']['type'] ?? null;
            $submission->information_md_media_secondary_vendor = $json_lustre_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_secondary_interface = $json_lustre_server_media['att']['interface'] ?? null;
            $submission->information_md_media_secondary_count = $json_lustre_server_media['att']['count'] ?? null;
            $submission->information_md_media_secondary_capacity = isset($json_lustre_server_media['att']['net capacity']) ? implode(' ', $json_lustre_server_media['att']['net capacity']) : null;
        }

        return $submission;
    }

    private function parse_spectrum($submission, $json_spectrum)
    {
        $submission->information_ds_software_version = $json_spectrum['att']['Version'] ?? null;
        $submission->information_md_software_version = $json_spectrum['att']['Version'] ?? null;

        // Data Server
        $json_spectrum_server = $this->find_information($json_spectrum, 'type', 'DATA SERVERS');
        $json_spectrum_server = $this->find_information($json_spectrum_server, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_spectrum_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_spectrum_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_spectrum_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_spectrum_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_spectrum_server_processor = $this->find_information($json_spectrum_server, 'type', 'PROCESSOR');

        if ($json_spectrum_server_processor) {
            $submission->information_ds_architecture = $json_spectrum_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_spectrum_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_spectrum_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_spectrum_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_spectrum_server_processor['att']['frequency']) ? implode(' ', $json_spectrum_server_processor['att']['frequency']) : null;
        }

        $json_spectrum_server_memory = $this->find_information($json_spectrum_server, 'type', 'MEMORY');

        if ($json_spectrum_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_spectrum_server_memory['att']['net capacity']) ? implode(' ', $json_spectrum_server_memory['att']['net capacity']) : null;
        }

        $json_spectrum_server_interconnect = $this->find_information($json_spectrum_server, 'type', 'INTERCONNECT');

        if ($json_spectrum_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_spectrum_server_interconnect['att']['type'] ?? null; // same as information_ds_network
            $submission->information_ds_interconnect_vendor = $json_spectrum_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_spectrum_server_interconnect['att']['peak throughput']) ? implode(' ', $json_spectrum_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_spectrum_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_spectrum_server_interconnect['att']['features']) ? (strpos($json_spectrum_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_ds_network = $submission->information_ds_interconnect_type;
        }

        $json_spectrum_server_media = $this->find_information($json_spectrum_server, 'type', 'STORAGEMEDIA');

        if ($json_spectrum_server_media) {
            $submission->information_ds_storage_type = $json_spectrum_server_media['att']['type'] ?? null;
            $submission->information_ds_storage_interface = $json_spectrum_server_media['att']['interface'] ?? null;
        }

        // Metadata Server
        $json_spectrum_server = $this->find_information($json_spectrum, 'type', 'METADATA SERVERS');
        $json_spectrum_server = $this->find_information($json_spectrum_server, 'type', 'SERVERS');

        $submission->information_md_nodes = $json_spectrum_server['att']['count'] ?? null;
        $submission->information_md_operating_system = $json_spectrum_server['att']['distribution'] ?? null;
        $submission->information_md_operating_system_version = $json_spectrum_server['att']['distribution version'] ?? null;
        $submission->information_md_kernel_version = $json_spectrum_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_spectrum_server_processor = $this->find_information($json_spectrum_server, 'type', 'PROCESSOR');

        if ($json_spectrum_server_processor) {
            $submission->information_md_architecture = $json_spectrum_server_processor['att']['architecture'] ?? null;
            $submission->information_md_model = $json_spectrum_server_processor['att']['model'] ?? null;
            $submission->information_md_sockets = $json_spectrum_server_processor['att']['sockets'] ?? null;
            $submission->information_md_cores_per_socket = $json_spectrum_server_processor['att']['cores per socket'] ?? null;
            $submission->information_md_clock = isset($json_spectrum_server_processor['att']['frequency']) ? implode(' ', $json_spectrum_server_processor['att']['frequency']) : null;
        }

        $json_spectrum_server_memory = $this->find_information($json_spectrum_server, 'type', 'MEMORY');

        if ($json_spectrum_server_memory) {
            $submission->information_md_volatile_memory_capacity = isset($json_spectrum_server_memory['att']['net capacity']) ? implode(' ', $json_spectrum_server_memory['att']['net capacity']) : null;
        }

        $json_spectrum_server_interconnect = $this->find_information($json_spectrum_server, 'type', 'INTERCONNECT');

        if ($json_spectrum_server_interconnect) {
            $submission->information_md_interconnect_type = $json_spectrum_server_interconnect['att']['type'] ?? null; // same as information_md_network
            $submission->information_md_interconnect_vendor = $json_spectrum_server_interconnect['att']['vendor'] ?? null;
            $submission->information_md_interconnect_bandwidth = isset($json_spectrum_server_interconnect['att']['peak throughput']) ? implode(' ', $json_spectrum_server_interconnect['att']['peak throughput']) : null;
            $submission->information_md_interconnect_links = $json_spectrum_server_interconnect['att']['links'] ?? null;
            $submission->information_md_interconnect_rdma = isset($json_spectrum_server_interconnect['att']['features']) ? (strpos($json_spectrum_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_md_network = $submission->information_md_interconnect_type;
        }

        $json_spectrum_server_media = $this->find_information($json_spectrum_server, 'type', 'STORAGEMEDIA', 1);

        if ($json_spectrum_server_media) {
            $submission->information_md_media_primary_type = $json_spectrum_server_media['att']['type'] ?? null; // same as information_md_storage_type
            $submission->information_md_media_primary_vendor = $json_spectrum_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_primary_interface = $json_spectrum_server_media['att']['interface'] ?? null; // same as information_md_storage_interface
            $submission->information_md_media_primary_count = $json_spectrum_server_media['att']['count'] ?? null;
            $submission->information_md_media_primary_capacity = isset($json_spectrum_server_media['att']['net capacity']) ? implode(' ', $json_spectrum_server_media['att']['net capacity']) : null;

            $submission->information_md_storage_type = $submission->information_md_media_primary_type;
            $submission->information_md_storage_interface = $submission->information_md_media_primary_interface;
        }

        $json_spectrum_server_media = $this->find_information($json_spectrum_server, 'type', 'STORAGEMEDIA', 2);

        if ($json_spectrum_server_media) {
            $submission->information_md_media_secondary_type = $json_spectrum_server_media['att']['type'] ?? null;
            $submission->information_md_media_secondary_vendor = $json_spectrum_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_secondary_interface = $json_spectrum_server_media['att']['interface'] ?? null;
            $submission->information_md_media_secondary_count = $json_spectrum_server_media['att']['count'] ?? null;
            $submission->information_md_media_secondary_capacity = isset($json_spectrum_server_media['att']['net capacity']) ? implode(' ', $json_spectrum_server_media['att']['net capacity']) : null;
        }

        return $submission;
    }

    private function parse_beegfs($submission, $json_beegfs)
    {
        $submission->information_ds_software_version = $json_beegfs['att']['version'] ?? null;
        $submission->information_md_software_version = $json_beegfs['att']['version'] ?? null;

        // Data Server
        $json_beegfs_server = $this->find_information($json_beegfs, 'type', 'STORAGE SERVER');

        $submission->information_ds_nodes = $json_beegfs_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_beegfs_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_beegfs_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_beegfs_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_beegfs_server_processor = $this->find_information($json_beegfs_server, 'type', 'PROCESSOR');

        if ($json_beegfs_server_processor) {
            $submission->information_ds_architecture = $json_beegfs_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_beegfs_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_beegfs_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_beegfs_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_beegfs_server_processor['att']['frequency']) ? implode(' ', $json_beegfs_server_processor['att']['frequency']) : null;
        }

        $json_beegfs_server_memory = $this->find_information($json_beegfs_server, 'type', 'MEMORY');

        if ($json_beegfs_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_beegfs_server_memory['att']['net capacity']) ? implode(' ', $json_beegfs_server_memory['att']['net capacity']) : null;
        }

        $json_beegfs_server_interconnect = $this->find_information($json_beegfs_server, 'type', 'INTERCONNECT');

        if ($json_beegfs_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_beegfs_server_interconnect['att']['type'] ?? null; // same as information_ds_network
            $submission->information_ds_interconnect_vendor = $json_beegfs_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_beegfs_server_interconnect['att']['peak throughput']) ? implode(' ', $json_beegfs_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_beegfs_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_beegfs_server_interconnect['att']['features']) ? (strpos($json_beegfs_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_ds_network = $submission->information_ds_interconnect_type;
        }

        $json_beegfs_server_media = $this->find_information($json_beegfs_server, 'type', 'STORAGEMEDIA');

        if ($json_beegfs_server_media) {
            $submission->information_ds_storage_type = $json_beegfs_server_media['att']['type'] ?? null;
            $submission->information_ds_storage_interface = $json_beegfs_server_media['att']['interface'] ?? null;
        }

        // Metadata Server
        $json_beegfs_server = $this->find_information($json_beegfs, 'type', 'METADATA SERVER');

        $submission->information_md_nodes = $json_beegfs_server['att']['count'] ?? null;
        $submission->information_md_operating_system = $json_beegfs_server['att']['distribution'] ?? null;
        $submission->information_md_operating_system_version = $json_beegfs_server['att']['distribution version'] ?? null;
        $submission->information_md_kernel_version = $json_beegfs_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_beegfs_server_processor = $this->find_information($json_beegfs_server, 'type', 'PROCESSOR');

        if ($json_beegfs_server_processor) {
            $submission->information_md_architecture = $json_beegfs_server_processor['att']['architecture'] ?? null;
            $submission->information_md_model = $json_beegfs_server_processor['att']['model'] ?? null;
            $submission->information_md_sockets = $json_beegfs_server_processor['att']['sockets'] ?? null;
            $submission->information_md_cores_per_socket = $json_beegfs_server_processor['att']['cores per socket'] ?? null;
            $submission->information_md_clock = isset($json_beegfs_server_processor['att']['frequency']) ? implode(' ', $json_beegfs_server_processor['att']['frequency']) : null;
        }

        $json_beegfs_server_memory = $this->find_information($json_beegfs_server, 'type', 'MEMORY');

        if ($json_beegfs_server_memory) {
            $submission->information_md_volatile_memory_capacity = isset($json_beegfs_server_memory['att']['net capacity']) ? implode(' ', $json_beegfs_server_memory['att']['net capacity']) : null;
        }

        $json_beegfs_server_interconnect = $this->find_information($json_beegfs_server, 'type', 'INTERCONNECT');

        if ($json_beegfs_server_interconnect) {
            $submission->information_md_interconnect_type = $json_beegfs_server_interconnect['att']['type'] ?? null; // same as information_md_network
            $submission->information_md_interconnect_vendor = $json_beegfs_server_interconnect['att']['vendor'] ?? null;
            $submission->information_md_interconnect_bandwidth = isset($json_beegfs_server_interconnect['att']['peak throughput']) ? implode(' ', $json_beegfs_server_interconnect['att']['peak throughput']) : null;
            $submission->information_md_interconnect_links = $json_beegfs_server_interconnect['att']['links'] ?? null;
            $submission->information_md_interconnect_rdma = isset($json_beegfs_server_interconnect['att']['features']) ? (strpos($json_beegfs_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_md_network = $submission->information_md_interconnect_type;
        }

        $json_beegfs_server_media = $this->find_information($json_beegfs_server, 'type', 'STORAGEMEDIA', 1);

        if ($json_beegfs_server_media) {
            $submission->information_md_media_primary_type = $json_beegfs_server_media['att']['type'] ?? null;
            $submission->information_md_media_primary_vendor = $json_beegfs_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_primary_interface = $json_beegfs_server_media['att']['interface'] ?? null;
            $submission->information_md_media_primary_count = $json_beegfs_server_media['att']['count'] ?? null;
            $submission->information_md_media_primary_capacity = isset($json_beegfs_server_media['att']['net capacity']) ? implode(' ', $json_beegfs_server_media['att']['net capacity']) : null;

            $submission->information_md_storage_type = $submission->information_md_media_primary_type;
            $submission->information_md_storage_interface = $submission->information_md_media_primary_interface;
        }

        $json_beegfs_server_media = $this->find_information($json_beegfs_server, 'type', 'STORAGEMEDIA', 2);

        if ($json_beegfs_server_media) {
            $submission->information_md_media_secondary_type = $json_beegfs_server_media['att']['type'] ?? null;
            $submission->information_md_media_secondary_vendor = $json_beegfs_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_secondary_interface = $json_beegfs_server_media['att']['interface'] ?? null;
            $submission->information_md_media_secondary_count = $json_beegfs_server_media['att']['count'] ?? null;
            $submission->information_md_media_secondary_capacity = isset($json_beegfs_server_media['att']['net capacity']) ? implode(' ', $json_beegfs_server_media['att']['net capacity']) : null;
        }

        return $submission;
    }

    private function parse_nas($submission, $json_nas)
    {
        // Data Server
        $json_nas_server = $this->find_information($json_nas, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_nas_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_nas_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_nas_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_nas_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_nas_server_processor = $this->find_information($json_nas_server, 'type', 'PROCESSOR');

        if ($json_nas_server_processor) {
            $submission->information_ds_architecture = $json_nas_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_nas_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_nas_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_nas_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_nas_server_processor['att']['frequency']) ? implode(' ', $json_nas_server_processor['att']['frequency']) : null;
        }

        $json_nas_server_memory = $this->find_information($json_nas_server, 'type', 'MEMORY');

        if ($json_nas_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_nas_server_memory['att']['net capacity']) ? implode(' ', $json_nas_server_memory['att']['net capacity']) : null;
        }

        $json_nas_server_interconnect = $this->find_information($json_nas_server, 'type', 'INTERCONNECT');

        if ($json_nas_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_nas_server_interconnect['att']['type'] ?? null; // same as information_ds_network
            $submission->information_ds_interconnect_vendor = $json_nas_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_nas_server_interconnect['att']['peak throughput']) ? implode(' ', $json_nas_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_nas_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_nas_server_interconnect['att']['features']) ? (strpos($json_nas_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_ds_network = $submission->information_ds_interconnect_type;
        }

        return $submission;
    }

    private function parse_other($submission, $json_other)
    {
        // The Other node carries no version of its own; the software version is
        // defined at the storage-system level and already parsed into
        // information_filesystem_version (see parse()).
        $submission->information_ds_software_version = $submission->information_filesystem_version ?? null;
        $submission->information_md_software_version = $submission->information_filesystem_version ?? null;

        // The Other scheme only defines a single Servers group, so the same
        // server set populates both the data-server and metadata-server fields.
        $json_other_server = $this->find_information($json_other, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_other_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_other_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_other_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_other_server['att']['kernel version'] ?? null;

        $submission->information_md_nodes = $json_other_server['att']['count'] ?? null;
        $submission->information_md_operating_system = $json_other_server['att']['distribution'] ?? null;
        $submission->information_md_operating_system_version = $json_other_server['att']['distribution version'] ?? null;
        $submission->information_md_kernel_version = $json_other_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_other_server_processor = $this->find_information($json_other_server, 'type', 'PROCESSOR');

        if ($json_other_server_processor) {
            $submission->information_ds_architecture = $json_other_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_other_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_other_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_other_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_other_server_processor['att']['frequency']) ? implode(' ', $json_other_server_processor['att']['frequency']) : null;

            $submission->information_md_architecture = $json_other_server_processor['att']['architecture'] ?? null;
            $submission->information_md_model = $json_other_server_processor['att']['model'] ?? null;
            $submission->information_md_sockets = $json_other_server_processor['att']['sockets'] ?? null;
            $submission->information_md_cores_per_socket = $json_other_server_processor['att']['cores per socket'] ?? null;
            $submission->information_md_clock = isset($json_other_server_processor['att']['frequency']) ? implode(' ', $json_other_server_processor['att']['frequency']) : null;
        }

        $json_other_server_memory = $this->find_information($json_other_server, 'type', 'MEMORY');

        if ($json_other_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_other_server_memory['att']['net capacity']) ? implode(' ', $json_other_server_memory['att']['net capacity']) : null;
            $submission->information_md_volatile_memory_capacity = isset($json_other_server_memory['att']['net capacity']) ? implode(' ', $json_other_server_memory['att']['net capacity']) : null;
        }

        $json_other_server_interconnect = $this->find_information($json_other_server, 'type', 'INTERCONNECT');

        if ($json_other_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_other_server_interconnect['att']['type'] ?? null; // same as information_ds_network
            $submission->information_ds_interconnect_vendor = $json_other_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_other_server_interconnect['att']['peak throughput']) ? implode(' ', $json_other_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_other_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_other_server_interconnect['att']['features']) ? (strpos($json_other_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_ds_network = $submission->information_ds_interconnect_type;

            $submission->information_md_interconnect_type = $json_other_server_interconnect['att']['type'] ?? null; // same as information_md_network
            $submission->information_md_interconnect_vendor = $json_other_server_interconnect['att']['vendor'] ?? null;
            $submission->information_md_interconnect_bandwidth = isset($json_other_server_interconnect['att']['peak throughput']) ? implode(' ', $json_other_server_interconnect['att']['peak throughput']) : null;
            $submission->information_md_interconnect_links = $json_other_server_interconnect['att']['links'] ?? null;
            $submission->information_md_interconnect_rdma = isset($json_other_server_interconnect['att']['features']) ? (strpos($json_other_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_md_network = $submission->information_md_interconnect_type;
        }

        // Storage media. The Other scheme has a single Servers group, so the same
        // media populates both the data-server and metadata-server fields, mirroring
        // parse_lustre(). Guarded so a missing StorageMedia leaves existing values
        // (e.g. the IO500-sourced DS storage type) untouched.
        $json_other_server_media = $this->find_information($json_other_server, 'type', 'STORAGEMEDIA', 1);

        if ($json_other_server_media) {
            $submission->information_ds_storage_type = $json_other_server_media['att']['type'] ?? null;
            $submission->information_ds_storage_interface = $json_other_server_media['att']['interface'] ?? null;

            $submission->information_md_media_primary_type = $json_other_server_media['att']['type'] ?? null; // same as information_md_storage_type
            $submission->information_md_media_primary_vendor = $json_other_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_primary_interface = $json_other_server_media['att']['interface'] ?? null; // same as information_md_storage_interface
            $submission->information_md_media_primary_count = $json_other_server_media['att']['count'] ?? null;
            $submission->information_md_media_primary_capacity = isset($json_other_server_media['att']['net capacity']) ? implode(' ', $json_other_server_media['att']['net capacity']) : null;

            $submission->information_md_storage_type = $submission->information_md_media_primary_type;
            $submission->information_md_storage_interface = $submission->information_md_media_primary_interface;
        }

        $json_other_server_media = $this->find_information($json_other_server, 'type', 'STORAGEMEDIA', 2);

        if ($json_other_server_media) {
            $submission->information_md_media_secondary_type = $json_other_server_media['att']['type'] ?? null;
            $submission->information_md_media_secondary_vendor = $json_other_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_secondary_interface = $json_other_server_media['att']['interface'] ?? null;
            $submission->information_md_media_secondary_count = $json_other_server_media['att']['count'] ?? null;
            $submission->information_md_media_secondary_capacity = isset($json_other_server_media['att']['net capacity']) ? implode(' ', $json_other_server_media['att']['net capacity']) : null;
        }

        return $submission;
    }

    private function parse_daos($submission, $json_daos)
    {
        $submission->information_ds_software_version = $json_daos['att']['Version'] ?? null;

        // Data Server
        $json_daos_server = $this->find_information($json_daos, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_daos_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_daos_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_daos_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_daos_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_daos_server_processor = $this->find_information($json_daos_server, 'type', 'PROCESSOR');

        if ($json_daos_server_processor) {
            $submission->information_ds_architecture = $json_daos_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_daos_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_daos_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_daos_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_daos_server_processor['att']['frequency']) ? implode(' ', $json_daos_server_processor['att']['frequency']) : null;
        }

        $json_daos_server_memory = $this->find_information($json_daos_server, 'type', 'MEMORY');

        if ($json_daos_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_daos_server_memory['att']['net capacity']) ? implode(' ', $json_daos_server_memory['att']['net capacity']) : null;
        }

        $json_daos_server_interconnect = $this->find_information($json_daos_server, 'type', 'INTERCONNECT');

        if ($json_daos_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_daos_server_interconnect['att']['type'] ?? null; // same as information_ds_network
            $submission->information_ds_interconnect_vendor = $json_daos_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_daos_server_interconnect['att']['peak throughput']) ? implode(' ', $json_daos_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_daos_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_daos_server_interconnect['att']['features']) ? (strpos($json_daos_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;

            $submission->information_ds_network = $submission->information_ds_interconnect_type;
        }

        return $submission;
    }

    private function find_information($array, $key, $value, $nth = 1)
    {
        if (is_array($array)) {
            $iterator = new \RecursiveArrayIterator($array);
            $recursive = new \RecursiveIteratorIterator(
                $iterator,
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $n = 1;

            foreach ($recursive as $k => $v) {
                if ($k === $key && strtolower($v) == strtolower($value)) {
                    if ($n == $nth) {
                        return $recursive->getSubIterator($recursive->getDepth() - 1)->current();
                    }

                    $n++;
                }
            }
        }

        return null;
    }
}
