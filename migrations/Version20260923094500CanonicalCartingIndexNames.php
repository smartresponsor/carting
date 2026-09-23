<?php

declare(strict_types=1);

namespace App\Carting\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923094500CanonicalCartingIndexNames extends AbstractMigration
{
    /** @var array<string, string> */
    private const array INDEX_RENAMES = [
        'idx_e99308e41ad5cdbf' => 'idx_cart_adjustment_cart_id',
        'uniq_e446888eddcc33' => 'uniq_cart_cart_token',
        'uniq_e1894cc9a737b21f' => 'uniq_cart_checkout_handoff_reference',
        'idx_f0fe25271ad5cdbf' => 'idx_cart_item_cart_id',
    ];

    public function getDescription(): string
    {
        return 'Rename residual Doctrine hash-derived Carting indexes to the semantic names already declared by current metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Carting canonical index-name migration supports PostgreSQL only.',
        );

        foreach (self::INDEX_RENAMES as $legacy => $canonical) {
            $this->addSql(sprintf(
                <<<'SQL'
DO $$
BEGIN
    IF to_regclass('public.%1$s') IS NOT NULL AND to_regclass('public.%2$s') IS NULL THEN
        EXECUTE 'ALTER INDEX %1$s RENAME TO %2$s';
    ELSIF to_regclass('public.%1$s') IS NOT NULL AND to_regclass('public.%2$s') IS NOT NULL THEN
        RAISE EXCEPTION 'Both legacy index %1$s and canonical index %2$s exist; manual reconciliation is required.';
    END IF;
END
$$
SQL,
                $legacy,
                $canonical,
            ));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException(
            'Canonical Carting schema-object naming convergence is intentionally irreversible.',
        );
    }
}

