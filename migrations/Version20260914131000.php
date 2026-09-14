<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260914131000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove redundant non-unique reference indexes already covered by unique constraints';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting production schema requires PostgreSQL.');

        $this->addSql('DROP INDEX IF EXISTS cart_cart_token_idx');
        $this->addSql('DROP INDEX IF EXISTS cart_checkout_handoff_reference_idx');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting production schema requires PostgreSQL.');

        $this->addSql('CREATE INDEX IF NOT EXISTS cart_cart_token_idx ON cart_cart (cart_token)');
        $this->addSql('CREATE INDEX IF NOT EXISTS cart_checkout_handoff_reference_idx ON cart_checkout_handoff (handoff_reference)');
    }
}
