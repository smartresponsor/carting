<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260921134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Persist optional producer provenance for Carting adjustments.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting adjustment provenance requires PostgreSQL.');
        $this->addSql('ALTER TABLE cart_adjustment ADD source_reference VARCHAR(191) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Carting adjustment provenance requires PostgreSQL.');
        $this->addSql('ALTER TABLE cart_adjustment DROP source_reference');
    }
}
