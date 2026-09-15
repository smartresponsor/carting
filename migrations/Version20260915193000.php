<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove redundant Carting indexes duplicated by canonical Doctrine indexes.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting schema cleanup requires PostgreSQL.');
        $this->addSql('DROP INDEX IF EXISTS idx_cart_adjustment_cart_id');
        $this->addSql('DROP INDEX IF EXISTS uniq_cart_cart_token');
        $this->addSql('DROP INDEX IF EXISTS uniq_cart_checkout_handoff_reference');
        $this->addSql('DROP INDEX IF EXISTS idx_cart_item_cart_id');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Redundant index cleanup follows current Carting ORM metadata.');
    }
}
