<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916182500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore canonical Carting unique indexes and Doctrine relation index names after the prior redundant-index cleanup.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting schema parity repair requires PostgreSQL.');

        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_cart_cart_token ON cart_cart (cart_token)');
        $this->addSql('DROP INDEX IF EXISTS idx_e99308e41ad5cdbf');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_cart_adjustment_cart_id ON cart_adjustment (cart_id)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_cart_checkout_handoff_reference ON cart_checkout_handoff (handoff_reference)');
        $this->addSql('DROP INDEX IF EXISTS idx_f0fe25271ad5cdbf');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_cart_item_cart_id ON cart_item (cart_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting schema parity repair requires PostgreSQL.');

        $this->addSql('ALTER INDEX IF EXISTS idx_cart_adjustment_cart_id RENAME TO idx_e99308e41ad5cdbf');
        $this->addSql('DROP INDEX IF EXISTS uniq_cart_cart_token');
        $this->addSql('DROP INDEX IF EXISTS uniq_cart_checkout_handoff_reference');
        $this->addSql('ALTER INDEX IF EXISTS idx_cart_item_cart_id RENAME TO idx_f0fe25271ad5cdbf');
    }
}
