<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002131500CanonicalCartRootTable extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename the duplicated Carting root table token from cart_cart to canonical cart.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting root-table convergence supports PostgreSQL only.');
        $this->abortIf($schema->hasTable('cart'), 'Canonical cart table already exists; manual reconciliation is required.');
        $this->abortIf(!$schema->hasTable('cart_cart'), 'Legacy cart_cart table is required for canonical convergence.');

        $this->addSql('ALTER TABLE cart_cart RENAME TO cart');
        $this->addSql('ALTER TABLE cart RENAME CONSTRAINT cart_cart_pkey TO cart_pkey');
        $this->addSql('ALTER INDEX IF EXISTS uniq_cart_cart_token RENAME TO uniq_cart_token');
        $this->addSql('ALTER INDEX IF EXISTS cart_cart_token_idx RENAME TO cart_token_idx');
        $this->addSql('ALTER INDEX IF EXISTS cart_cart_owner_reference_idx RENAME TO cart_owner_reference_idx');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'Canonical Carting root naming is intentionally irreversible.',
        );
    }
}
