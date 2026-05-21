<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddChecklistToReleases extends AbstractMigration
{
    public function change(): void
    {
        $this->table('releases')
            ->addColumn('checklist', 'json', [
                'default' => null,
                'null' => true,
            ])
            ->update();
    }
}
