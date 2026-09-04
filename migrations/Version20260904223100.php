<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260904223100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Finalize checkout_pending lifecycle, price-estimate adjustments, and downstream acceptance evidence';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting production schema requires PostgreSQL.');

        $this->addSql('ALTER TABLE cart_cart DROP CONSTRAINT IF EXISTS chk_cart_status');
        $this->addSql("ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_status CHECK (status IN ('active', 'checkout_pending', 'converted', 'merged', 'abandoned', 'expired'))");
        $this->addSql('ALTER TABLE cart_adjustment DROP CONSTRAINT IF EXISTS chk_cart_adjustment_type');
        $this->addSql("ALTER TABLE cart_adjustment ADD CONSTRAINT chk_cart_adjustment_type CHECK (type IN ('price_estimate', 'promotion', 'tax_estimate', 'shipping_estimate', 'manual'))");
        $this->addSql('ALTER TABLE cart_checkout_handoff ADD COLUMN IF NOT EXISTS downstream_reference VARCHAR(191) DEFAULT NULL');
        $this->addSql('ALTER TABLE cart_checkout_handoff ADD COLUMN IF NOT EXISTS accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_cart_checkout_handoff_downstream_reference ON cart_checkout_handoff (downstream_reference)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting production schema requires PostgreSQL.');
        $this->abortIf((bool) $this->connection->fetchOne("SELECT EXISTS (SELECT 1 FROM cart_cart WHERE status IN ('checkout_pending', 'merged'))"), 'Cannot remove checkout_pending or merged while carts still use those states.');
        $this->abortIf((bool) $this->connection->fetchOne("SELECT EXISTS (SELECT 1 FROM cart_adjustment WHERE type = 'price_estimate')"), 'Cannot remove price_estimate while cart adjustments still use that type.');
        $this->abortIf((bool) $this->connection->fetchOne('SELECT EXISTS (SELECT 1 FROM cart_checkout_handoff WHERE downstream_reference IS NOT NULL OR accepted_at IS NOT NULL)'), 'Cannot remove checkout acceptance evidence while accepted handoffs exist.');

        $this->addSql('ALTER TABLE cart_cart DROP CONSTRAINT IF EXISTS chk_cart_status');
        $this->addSql("ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_status CHECK (status IN ('active', 'converted', 'abandoned', 'expired'))");
        $this->addSql('ALTER TABLE cart_adjustment DROP CONSTRAINT IF EXISTS chk_cart_adjustment_type');
        $this->addSql("ALTER TABLE cart_adjustment ADD CONSTRAINT chk_cart_adjustment_type CHECK (type IN ('promotion', 'tax_estimate', 'shipping_estimate', 'manual'))");
        $this->addSql('DROP INDEX IF EXISTS idx_cart_checkout_handoff_downstream_reference');
        $this->addSql('ALTER TABLE cart_checkout_handoff DROP COLUMN IF EXISTS accepted_at');
        $this->addSql('ALTER TABLE cart_checkout_handoff DROP COLUMN IF EXISTS downstream_reference');
    }
}
