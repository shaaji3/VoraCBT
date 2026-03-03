<?php

declare(strict_types=1);

use App\Core\Database\Migration\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

class AddNegativeMarkingToExamTemplates extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('exam_templates')) {
            return;
        }

        $table = $schema->getTable('exam_templates');

        if (!$table->hasColumn('negative_marking_enabled')) {
            $table->addColumn('negative_marking_enabled', Types::BOOLEAN)->setDefault(false);
        }

        if (!$table->hasColumn('negative_mark_per_wrong')) {
            $table->addColumn('negative_mark_per_wrong', Types::FLOAT)->setDefault(0);
        }
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('exam_templates')) {
            return;
        }

        $table = $schema->getTable('exam_templates');
        if ($table->hasColumn('negative_mark_per_wrong')) {
            $table->dropColumn('negative_mark_per_wrong');
        }
        if ($table->hasColumn('negative_marking_enabled')) {
            $table->dropColumn('negative_marking_enabled');
        }
    }
}
