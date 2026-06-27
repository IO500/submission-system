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
 * Backfills the per-server storage/software fields that some filesystem parsers
 * never populated (notably OTHER, and the storage media of NAS/DAOS/WekaIO),
 * from each submission's stored JSON.
 *
 * Safety model (this is what makes the backfill safe where the live parser is not):
 *   1. NULL-only  — a column is written ONLY if its current DB value is NULL or ''.
 *                   Correctly-parsed values are never overwritten.
 *   2. Content-aware — a value is written ONLY if the candidate extracted from the
 *                   JSON is itself non-empty. Empty "scaffolding" StorageMedia nodes
 *                   (every submission embeds empty DAOS/NAS/etc. nodes) can therefore
 *                   never blank out or wrongly fill a field.
 * Together these make the command idempotent and regression-free: re-running it, or
 * running it on an already-correct row, changes nothing.
 *
 * Dry-run is the DEFAULT. Pass --commit to actually write.
 *
 * Runs against the 'default' datasource, which in this project points at the
 * shadow database (config/app_local.php). Do NOT point it at production without
 * explicit authorisation.
 */
class BackfillStorageInfoCommand extends Command
{
    /** Server groups that describe DATA servers. */
    private const DS_GROUPS = ['OSS', 'DATA SERVERS', 'STORAGE SERVER'];

    /** Server groups that describe METADATA servers. */
    private const MD_GROUPS = ['MDS', 'METADATA SERVERS', 'METADATA SERVER'];

    /** Generic server group used by single-tier schemes (OTHER, NAS, DAOS, WekaIO). */
    private const GENERIC_GROUPS = ['SERVERS'];

    public static function defaultName(): string
    {
        return 'backfill_storage_info';
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription(
            'Backfill per-server storage type/interface and software version from each ' .
            'submission\'s stored JSON. NULL-only and content-aware: never overwrites an ' .
            'existing value and never writes an empty one. Dry-run unless --commit is given.'
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
            'help'    => 'Report columns whose existing DB value differs from the JSON value. ' .
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

            $candidates = $this->extract($json);
            if ($candidates === null) {
                continue;
            }

            // Report-only mode: flag columns where the DB already holds a non-empty
            // value that differs from the JSON value. Never writes.
            if ($showConflicts) {
                foreach ($candidates as $col => $value) {
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

            // Keep only columns that are currently empty AND have a non-empty candidate.
            $updates = [];
            foreach ($candidates as $col => $value) {
                if (!$this->isEmpty($value) && $this->isEmpty($row[$col] ?? null)) {
                    $updates[$col] = $value;
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
     * Extract candidate values from a submission's JSON, content-aware (only
     * non-empty values are returned).
     *
     * @param array<mixed> $json
     * @return array<string,string|null>|null
     */
    private function extract(array $json): ?array
    {
        $ss = $this->findInformation($json, 'type', 'STORAGESYSTEM');
        if ($ss === null) {
            return null;
        }

        $version = $this->nonEmpty($ss['att']['version'] ?? null);

        // Collect non-empty StorageMedia under the storage system, classified as DS or MD
        // by their nearest enclosing server group. Empty scaffolding media are ignored.
        $found = ['DS' => null, 'MD' => null];
        $this->collectMedia($ss, null, false, $found);

        $dsMedia = $found['DS'];
        $mdMedia = $found['MD'];

        // Single-tier schemes (OTHER/NAS/DAOS/WekaIO) describe their hardware under one
        // generic SERVERS group and have no metadata tier: the same media describes both
        // sides. Mirror DS -> MD, but only when the DS media itself came from a generic
        // group (so a dedicated-data-tier FS missing its MDS is left alone, not mirrored).
        if ($mdMedia === null && $dsMedia !== null && !empty($dsMedia['generic'])) {
            $mdMedia = $dsMedia;
        }

        return [
            'information_ds_storage_type'      => $dsMedia['type'] ?? null,
            'information_ds_storage_interface' => $dsMedia['interface'] ?? null,
            'information_ds_software_version'  => $version,
            'information_md_storage_type'      => $mdMedia['type'] ?? null,
            'information_md_storage_interface' => $mdMedia['interface'] ?? null,
            'information_md_software_version'  => $version,
        ];
    }

    /**
     * Recursively walk the storage-system subtree, tracking whether we are inside a
     * data-server or metadata-server group, and record the first non-empty
     * StorageMedia for each side.
     *
     * @param array<mixed> $node
     * @param string|null  $cur     'DS' | 'MD' | null (current server context)
     * @param bool         $generic whether $cur was established by a generic SERVERS group
     * @param array{DS:?array,MD:?array} $found
     */
    private function collectMedia(array $node, ?string $cur, bool $generic, array &$found): void
    {
        $type = isset($node['type']) ? strtoupper((string)$node['type']) : '';

        if (in_array($type, self::MD_GROUPS, true)) {
            $cur = 'MD';
            $generic = false;
        } elseif (in_array($type, self::DS_GROUPS, true)) {
            $cur = 'DS';
            $generic = false;
        } elseif (in_array($type, self::GENERIC_GROUPS, true)) {
            // Generic SERVERS: keep an existing MD/DS context, otherwise treat as DS
            // and remember the media came from a generic (single-tier) group.
            if ($cur === null) {
                $cur = 'DS';
                $generic = true;
            }
        }

        if ($type === 'STORAGEMEDIA') {
            $side = $cur ?? 'DS';
            if ($found[$side] === null) {
                $mtype = $this->nonEmpty($node['att']['type'] ?? null);
                $iface = $this->nonEmpty($node['att']['interface'] ?? null);
                if ($mtype !== null || $iface !== null) {
                    $found[$side] = ['type' => $mtype, 'interface' => $iface, 'generic' => $generic];
                }
            }
        }

        foreach ($node['childs'] ?? [] as $child) {
            if (is_array($child)) {
                $this->collectMedia($child, $cur, $generic, $found);
            }
        }
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
     * @param array<string,string|null> $updates
     */
    private function updateRow(\Cake\Database\Connection $db, int $id, array $updates): void
    {
        $set = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($updates)));
        $db->execute(
            "UPDATE submissions SET $set WHERE id = ?",
            array_merge(array_values($updates), [$id])
        );
    }

    /**
     * Recursively search $array for an element where $key === $value
     * (case-insensitive) and return the node that contains it.
     * Mirrors SubmissionsController::find_information().
     *
     * @param array<mixed> $array
     * @return array<mixed>|null
     */
    private function findInformation(array $array, string $key, string $value): ?array
    {
        $iterator = new \RecursiveArrayIterator($array);
        $recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($recursive as $k => $v) {
            if ($k === $key && is_scalar($v) && strtolower((string)$v) === strtolower($value)) {
                return $recursive->getSubIterator($recursive->getDepth() - 1)->current();
            }
        }

        return null;
    }

    private function nonEmpty(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }
        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || trim((string)$value) === '';
    }

    private function show(mixed $value): string
    {
        return $this->isEmpty($value) ? '(empty)' : (string)$value;
    }
}
