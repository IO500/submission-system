<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddProductionFlagToSubmissions extends AbstractMigration
{
    public function change(): void
    {
        $this->table('submissions')
            ->addColumn('information_production', 'boolean', [
                'default' => false,
                'null' => false,
                'after' => 'information_10_node_challenge',
            ])
            ->update();
    }
}
