<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddAcknowledgementsToSubmissions extends AbstractMigration
{
    public function change(): void
    {
        $this->table('submissions')
            ->addColumn('acknowledged_rules', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'information_production',
            ])
            ->addColumn('acknowledged_publication', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'acknowledged_rules',
            ])
            ->update();
    }
}
