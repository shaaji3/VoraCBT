<?php

declare(strict_types=1);

use App\Core\Database\Migration\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

class HardenResultSyncPackageSchema extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('result_sync_packages')) {
            return;
        }

        $table = $schema->getTable('result_sync_packages');

        if (!$table->hasColumn('idempotency_key')) {
            $table->addColumn('idempotency_key', Types::STRING)->setLength(64)->setNotnull(false);
        }

        if (!$table->hasColumn('payload_hash')) {
            $table->addColumn('payload_hash', Types::STRING)->setLength(64)->setNotnull(false);
        }

        if (!$table->hasColumn('signed_at')) {
            $table->addColumn('signed_at', Types::DATETIME_MUTABLE)->setNotnull(false);
        }

        if (!$table->hasIndex('uniq_result_sync_idempotency_key')) {
            $table->addUniqueIndex(['idempotency_key'], 'uniq_result_sync_idempotency_key');
        }

        if (!$table->hasIndex('idx_result_sync_payload_hash')) {
            $table->addIndex(['payload_hash'], 'idx_result_sync_payload_hash');
        }

        if (!$table->hasIndex('idx_result_sync_status_created')) {
            $table->addIndex(['status', 'created_at'], 'idx_result_sync_status_created');
        }
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('result_sync_packages')) {
            return;
        }

        $table = $schema->getTable('result_sync_packages');

        foreach (['idx_result_sync_status_created', 'idx_result_sync_payload_hash', 'uniq_result_sync_idempotency_key'] as $index) {
            if ($table->hasIndex($index)) {
                $table->dropIndex($index);
            }
        }

        foreach (['signed_at', 'payload_hash', 'idempotency_key'] as $column) {
            if ($table->hasColumn($column)) {
                $table->dropColumn($column);
            }
        }
    }
}
