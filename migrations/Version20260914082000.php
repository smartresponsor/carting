<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20260914082000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize Carting lifecycle audit columns through Objecting and align cart/checkout schema with current runtime states';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting production schema requires PostgreSQL.');

        foreach (['cart_cart', 'cart_item', 'cart_checkout_handoff'] as $table) {
            $this->addSql(sprintf('ALTER TABLE %s ADD COLUMN IF NOT EXISTS created_by VARCHAR(190) DEFAULT NULL', $table));
            $this->addSql(sprintf('ALTER TABLE %s ADD COLUMN IF NOT EXISTS modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL', $table));
            $this->addSql(sprintf('ALTER TABLE %s ADD COLUMN IF NOT EXISTS modified_by VARCHAR(190) DEFAULT NULL', $table));
        }

        $this->addSql(<<<'SQL'
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'cart_cart' AND column_name = 'updated_at') THEN
        EXECUTE 'UPDATE cart_cart SET modified_at = updated_at WHERE modified_at IS NULL';
    END IF;
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'cart_item' AND column_name = 'updated_at') THEN
        EXECUTE 'UPDATE cart_item SET modified_at = updated_at WHERE modified_at IS NULL';
    END IF;
END $$
SQL);

        $this->addSql('ALTER TABLE cart_cart DROP CONSTRAINT IF EXISTS chk_cart_timestamps');
        $this->addSql('ALTER TABLE cart_item DROP CONSTRAINT IF EXISTS chk_cart_item_timestamps');
        $this->addSql('ALTER TABLE cart_cart DROP CONSTRAINT IF EXISTS chk_cart_status');

        $this->addSql('ALTER TABLE cart_cart DROP COLUMN IF EXISTS updated_at');
        $this->addSql('ALTER TABLE cart_item DROP COLUMN IF EXISTS updated_at');

        $this->addSql('ALTER TABLE cart_checkout_handoff ADD COLUMN IF NOT EXISTS downstream_reference VARCHAR(191) DEFAULT NULL');
        $this->addSql('ALTER TABLE cart_checkout_handoff ADD COLUMN IF NOT EXISTS accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');

        $this->addSql("ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_status CHECK (status IN ('active', 'checkout_pending', 'converted', 'merged', 'abandoned', 'expired'))");
        $this->addSql('ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_timestamps CHECK ((modified_at IS NULL OR modified_at >= created_at) AND (expires_at IS NULL OR expires_at >= created_at))');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT chk_cart_item_timestamps CHECK (modified_at IS NULL OR modified_at >= created_at)');
        $this->addSql('ALTER TABLE cart_checkout_handoff ADD CONSTRAINT chk_cart_handoff_timestamps CHECK ((modified_at IS NULL OR modified_at >= created_at) AND (accepted_at IS NULL OR accepted_at >= created_at))');
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Objecting audit normalization intentionally removes legacy updated_at columns after deterministic backfill.');
    }
}
