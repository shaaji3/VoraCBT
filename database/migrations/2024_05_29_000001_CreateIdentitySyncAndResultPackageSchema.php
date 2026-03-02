<?php

declare(strict_types=1);

use App\Core\Database\Migration\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

class CreateIdentitySyncAndResultPackageSchema extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('identity_sync_cache')) {
            $table = $schema->createTable('identity_sync_cache');
            $table->addColumn('id', Types::GUID);
            $table->addColumn('entity_type', Types::STRING)->setLength(20);
            $table->addColumn('external_id', Types::STRING)->setLength(100);
            $table->addColumn('full_name', Types::STRING)->setLength(255)->setNotnull(false);
            $table->addColumn('class_id', Types::STRING)->setLength(100)->setNotnull(false);
            $table->addColumn('session_id', Types::STRING)->setLength(50)->setNotnull(false);
            $table->addColumn('term_id', Types::STRING)->setLength(50)->setNotnull(false);
            $table->addColumn('role', Types::STRING)->setLength(50)->setNotnull(false);
            $table->addColumn('email', Types::STRING)->setLength(255)->setNotnull(false);
            $table->addColumn('status', Types::STRING)->setLength(20)->setDefault('active');
            $table->addColumn('is_read_only', Types::BOOLEAN)->setDefault(true);
            $table->addColumn('payload', Types::JSON)->setNotnull(false);
            $table->addColumn('updated_at', Types::DATETIME_MUTABLE);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['entity_type', 'external_id']);
            $table->addIndex(['entity_type', 'class_id', 'session_id']);
        }

        if (!$schema->hasTable('student_identity_mappings')) {
            $table = $schema->createTable('student_identity_mappings');
            $table->addColumn('id', Types::GUID);
            $table->addColumn('external_student_ref', Types::STRING)->setLength(100);
            $table->addColumn('sms_student_id', Types::STRING)->setLength(100)->setNotnull(false);
            $table->addColumn('user_id', Types::GUID)->setNotnull(false);
            $table->addColumn('created_at', Types::DATETIME_MUTABLE);
            $table->addColumn('updated_at', Types::DATETIME_MUTABLE);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['external_student_ref']);
            $table->addIndex(['sms_student_id']);
            $table->addForeignKeyConstraint('users', ['user_id'], ['id'], ['onDelete' => 'SET NULL']);
        }

        if (!$schema->hasTable('result_sync_packages')) {
            $table = $schema->createTable('result_sync_packages');
            $table->addColumn('id', Types::GUID);
            $table->addColumn('exam_id', Types::GUID)->setNotnull(false);
            $table->addColumn('exam_session_id', Types::GUID)->setNotnull(false);
            $table->addColumn('mode', Types::STRING)->setLength(20);
            $table->addColumn('payload', Types::JSON);
            $table->addColumn('signature', Types::STRING)->setLength(255);
            $table->addColumn('status', Types::STRING)->setLength(30)->setDefault('generated');
            $table->addColumn('created_by', Types::GUID)->setNotnull(false);
            $table->addColumn('created_at', Types::DATETIME_MUTABLE);
            $table->addColumn('updated_at', Types::DATETIME_MUTABLE);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['mode', 'status']);
            $table->addForeignKeyConstraint('users', ['created_by'], ['id'], ['onDelete' => 'SET NULL']);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['result_sync_packages', 'student_identity_mappings', 'identity_sync_cache'] as $table) {
            if ($schema->hasTable($table)) {
                $schema->dropTable($table);
            }
        }
    }
}
