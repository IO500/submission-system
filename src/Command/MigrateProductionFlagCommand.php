<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

/**
 * bin/cake migrate_production_flag
 *
 * Backfills information_production on existing submissions.
 *
 * For historical data the most reliable signal is list membership: any
 * submission that appears in a "Production" or "10 Node Production" list
 * (across all releases / BoFs) is a production system. Reading the stored
 * JSON files is unreliable here because most of them are no longer on disk.
 *
 * New submissions get information_production set directly from the JSON
 * (StorageSystem "usage" attribute) at submission time, so they do not need
 * this command.
 */
class MigrateProductionFlagCommand extends Command
{
    /**
     * Type URLs whose lists imply a production system. Matched on `url`
     * (not numeric id) because the url is the stable identifier the app
     * routes on and is guaranteed identical across databases.
     */
    private const PRODUCTION_TYPE_URLS = ['production', 'ten-production'];

    public static function defaultName(): string
    {
        return 'migrate_production_flag';
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription(
            'Backfill information_production for existing submissions based on ' .
            'membership in Production / 10 Node Production lists.'
        );

        $parser->addOption('dry-run', [
            'boolean' => true,
            'default' => false,
            'help'    => 'Print what would be updated without writing to the database.',
        ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $dryRun = (bool)$args->getOption('dry-run');

        if ($dryRun) {
            $io->out('<warning>Dry-run mode — no changes will be written.</warning>');
        }

        /** @var \Cake\Database\Connection $db */
        $db = ConnectionManager::get('default');

        $placeholders = implode(',', array_fill(0, count(self::PRODUCTION_TYPE_URLS), '?'));

        // Submissions that appear in any production / 10-node-production list.
        $rows = $db->execute(
            'SELECT DISTINCT ls.submission_id AS id ' .
            'FROM listings_submissions ls ' .
            'JOIN listings l ON l.id = ls.listing_id ' .
            'JOIN types t ON t.id = l.type_id ' .
            "WHERE t.url IN ({$placeholders})",
            self::PRODUCTION_TYPE_URLS
        )->fetchAll('assoc');

        $productionIds = array_map(static fn ($r) => (int)$r['id'], $rows);

        $io->out(count($productionIds) . ' submission(s) belong to a production list.');

        if (empty($productionIds)) {
            $io->out('Nothing to update.');

            return self::CODE_SUCCESS;
        }

        if ($dryRun) {
            $io->verbose('Would set information_production=1 for IDs: ' . implode(', ', $productionIds));
            $io->out('Dry-run complete — no changes written.');

            return self::CODE_SUCCESS;
        }

        // Reset, then flag the production submissions so the column reflects
        // current list membership exactly.
        $db->execute('UPDATE submissions SET information_production = 0');

        $idPlaceholders = implode(',', array_fill(0, count($productionIds), '?'));
        $db->execute(
            "UPDATE submissions SET information_production = 1 WHERE id IN ({$idPlaceholders})",
            $productionIds
        );

        $io->out('Done. Flagged ' . count($productionIds) . ' submission(s) as production.');

        return self::CODE_SUCCESS;
    }
}
