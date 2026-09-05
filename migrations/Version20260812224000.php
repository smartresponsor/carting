<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20260812224000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adopt or create the Carting PostgreSQL baseline with integrity constraints, indexes, FKs, and optimistic-lock safeguards';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting production schema requires PostgreSQL.');

        $this->adoptOrCreateCart($schema);
        $this->adoptOrCreateItem($schema);
        $this->adoptOrCreateAdjustment($schema);
        $this->adoptOrCreateHandoff($schema);

        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_cart_cart_token ON cart_cart (cart_token)');
        $this->addSql('CREATE INDEX IF NOT EXISTS cart_cart_token_idx ON cart_cart (cart_token)');
        $this->addSql('CREATE INDEX IF NOT EXISTS cart_cart_owner_reference_idx ON cart_cart (owner_reference)');
        $this->addSql('CREATE INDEX IF NOT EXISTS cart_item_offer_reference_idx ON cart_item (offer_reference)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_cart_item_cart_id ON cart_item (cart_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_cart_adjustment_cart_id ON cart_adjustment (cart_id)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS cart_checkout_handoff_cart_unique ON cart_checkout_handoff (cart_id)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_cart_checkout_handoff_reference ON cart_checkout_handoff (handoff_reference)');
        $this->addSql('CREATE INDEX IF NOT EXISTS cart_checkout_handoff_reference_idx ON cart_checkout_handoff (handoff_reference)');

        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_cart_item_cart') THEN ALTER TABLE cart_item ADD CONSTRAINT fk_cart_item_cart FOREIGN KEY (cart_id) REFERENCES cart_cart (id) ON DELETE CASCADE; END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_cart_adjustment_cart') THEN ALTER TABLE cart_adjustment ADD CONSTRAINT fk_cart_adjustment_cart FOREIGN KEY (cart_id) REFERENCES cart_cart (id) ON DELETE CASCADE; END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'fk_cart_checkout_handoff_cart') THEN ALTER TABLE cart_checkout_handoff ADD CONSTRAINT fk_cart_checkout_handoff_cart FOREIGN KEY (cart_id) REFERENCES cart_cart (id) ON DELETE CASCADE; END IF; END $$");

        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_token_nonempty') THEN ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_token_nonempty CHECK (btrim(cart_token) <> ''); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_owner_nonempty') THEN ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_owner_nonempty CHECK (owner_reference IS NULL OR btrim(owner_reference) <> ''); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_currency') THEN ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_currency CHECK (currency_code ~ '^[A-Z]{3}$'); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_status') THEN ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_status CHECK (status IN ('active', 'converted', 'abandoned', 'expired')); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_timestamps') THEN ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_timestamps CHECK (updated_at >= created_at AND (expires_at IS NULL OR expires_at >= created_at)); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_version_positive') THEN ALTER TABLE cart_cart ADD CONSTRAINT chk_cart_version_positive CHECK (version >= 1); END IF; END $$");

        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_item_offer_nonempty') THEN ALTER TABLE cart_item ADD CONSTRAINT chk_cart_item_offer_nonempty CHECK (btrim(offer_reference) <> ''); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_item_title_nonempty') THEN ALTER TABLE cart_item ADD CONSTRAINT chk_cart_item_title_nonempty CHECK (btrim(title_snapshot) <> ''); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_item_price_nonnegative') THEN ALTER TABLE cart_item ADD CONSTRAINT chk_cart_item_price_nonnegative CHECK (unit_price_minor >= 0); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_item_currency') THEN ALTER TABLE cart_item ADD CONSTRAINT chk_cart_item_currency CHECK (currency_code ~ '^[A-Z]{3}$'); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_item_quantity_positive') THEN ALTER TABLE cart_item ADD CONSTRAINT chk_cart_item_quantity_positive CHECK (quantity >= 1); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_item_timestamps') THEN ALTER TABLE cart_item ADD CONSTRAINT chk_cart_item_timestamps CHECK (updated_at >= created_at); END IF; END $$");

        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_adjustment_type') THEN ALTER TABLE cart_adjustment ADD CONSTRAINT chk_cart_adjustment_type CHECK (type IN ('promotion', 'tax_estimate', 'shipping_estimate', 'manual')); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_adjustment_label_nonempty') THEN ALTER TABLE cart_adjustment ADD CONSTRAINT chk_cart_adjustment_label_nonempty CHECK (btrim(label) <> ''); END IF; END $$");
        $this->addSql("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'chk_cart_handoff_reference_nonempty') THEN ALTER TABLE cart_checkout_handoff ADD CONSTRAINT chk_cart_handoff_reference_nonempty CHECK (btrim(handoff_reference) <> ''); END IF; END $$");
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration('Carting baseline adoption is intentionally irreversible; destructive rollback must never drop production cart data implicitly.');
    }

    private function adoptOrCreateCart(Schema $schema): void
    {
        if ($schema->hasTable('cart_cart')) {
            $this->assertColumns($schema, 'cart_cart', ['id', 'cart_token', 'owner_reference', 'currency_code', 'status', 'created_at', 'updated_at', 'expires_at', 'version']);
            return;
        }

        $this->addSql("CREATE TABLE cart_cart (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, cart_token VARCHAR(96) NOT NULL, owner_reference VARCHAR(191) DEFAULT NULL, currency_code VARCHAR(3) NOT NULL, status VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, version INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))");
    }

    private function adoptOrCreateItem(Schema $schema): void
    {
        if ($schema->hasTable('cart_item')) {
            $this->assertColumns($schema, 'cart_item', ['id', 'cart_id', 'offer_reference', 'title_snapshot', 'unit_price_minor', 'currency_code', 'quantity', 'metadata', 'created_at', 'updated_at']);
            return;
        }

        $this->addSql("CREATE TABLE cart_item (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, cart_id INT NOT NULL, offer_reference VARCHAR(191) NOT NULL, title_snapshot VARCHAR(255) NOT NULL, unit_price_minor INT NOT NULL, currency_code VARCHAR(3) NOT NULL, quantity INT NOT NULL, metadata JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
    }

    private function adoptOrCreateAdjustment(Schema $schema): void
    {
        if ($schema->hasTable('cart_adjustment')) {
            $this->assertColumns($schema, 'cart_adjustment', ['id', 'cart_id', 'type', 'label', 'amount_minor']);
            return;
        }

        $this->addSql("CREATE TABLE cart_adjustment (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, cart_id INT NOT NULL, type VARCHAR(32) NOT NULL, label VARCHAR(191) NOT NULL, amount_minor INT NOT NULL, PRIMARY KEY(id))");
    }

    private function adoptOrCreateHandoff(Schema $schema): void
    {
        if ($schema->hasTable('cart_checkout_handoff')) {
            $this->assertColumns($schema, 'cart_checkout_handoff', ['id', 'cart_id', 'handoff_reference', 'payload', 'created_at']);
            return;
        }

        $this->addSql("CREATE TABLE cart_checkout_handoff (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, cart_id INT NOT NULL, handoff_reference VARCHAR(96) NOT NULL, payload JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
    }

    /** @param list<string> $columns */
    private function assertColumns(Schema $schema, string $tableName, array $columns): void
    {
        $table = $schema->getTable($tableName);
        foreach ($columns as $column) {
            $this->abortIf(!$table->hasColumn($column), sprintf('Existing %s table is missing required column "%s"; refusing unsafe baseline adoption.', $tableName, $column));
        }
    }
}
