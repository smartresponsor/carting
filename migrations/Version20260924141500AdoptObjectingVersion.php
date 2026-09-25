<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924141500AdoptObjectingVersion extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the Objecting-owned optimistic-version etag field to Carting cart state.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Carting Objecting version migration supports PostgreSQL only.',
        );

        $this->addSql('ALTER TABLE cart_cart ADD etag VARCHAR(128) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Carting Objecting version migration supports PostgreSQL only.',
        );

        $this->addSql('ALTER TABLE cart_cart DROP etag');
    }
}
