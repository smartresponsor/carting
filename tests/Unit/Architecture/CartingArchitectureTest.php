<?php

declare(strict_types=1);

namespace App\Carting\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class CartingArchitectureTest extends TestCase
{
    public function testLegacyCatchAllDirectoriesDoNotExist(): void
    {
        self::assertDirectoryDoesNotExist($this->root() . '/src/Value');
        self::assertDirectoryDoesNotExist($this->root() . '/src/Surface');
    }

    public function testDtoAndServiceInterfaceFilesUseCanonicalSuffixes(): void
    {
        foreach ($this->phpFiles($this->root() . '/src/DTO') as $file) {
            self::assertStringEndsWith('DTO.php', $file->getFilename());
        }

        foreach ($this->phpFiles($this->root() . '/src/ServiceInterface') as $file) {
            self::assertStringEndsWith('Interface.php', $file->getFilename());
        }
    }

    public function testApplicationServicesDoNotImportConcreteRepositories(): void
    {
        foreach ([$this->root() . '/src/Service', $this->root() . '/src/Controller'] as $directory) {
            foreach ($this->phpFiles($directory) as $file) {
                $contents = file_get_contents($file->getPathname());
                self::assertIsString($contents);
                self::assertStringNotContainsString(
                    'use App\\Carting\\Repository\\',
                    $contents,
                    sprintf('Concrete repository import is forbidden in %s.', $file->getPathname()),
                );
            }
        }
    }

    public function testTestsStayUnderCanonicalUnitOrIntegrationTrees(): void
    {
        foreach (['Service', 'Entity', 'Doctrine'] as $legacyDirectory) {
            self::assertDirectoryDoesNotExist($this->root() . '/tests/' . $legacyDirectory);
        }
    }

    public function testDevelopmentComposerUsesCanonicalPlatformDependencyContour(): void
    {
        $composer = file_get_contents($this->root() . '/composer.json');
        self::assertIsString($composer);

        foreach (['cruding/crud', 'collectioning/collection', 'tabling/table', 'viewing/view', 'interfacing/interface', 'objecting/object'] as $package) {
            self::assertStringContainsString(sprintf('"%s": "dev-master"', $package), $composer);
            self::assertStringContainsString(sprintf('"versions": { "%s": "dev-master" }', $package), $composer);
        }
    }

    public function testProductionComposerUsesPackagedPlatformDependencies(): void
    {
        $composer = file_get_contents($this->root() . '/composer.prod.json');
        self::assertIsString($composer);

        self::assertStringNotContainsString('"type": "path"', $composer);
        foreach (['cruding/crud', 'collectioning/collection', 'tabling/table', 'viewing/view', 'interfacing/interface', 'objecting/object'] as $package) {
            self::assertStringContainsString(sprintf('"%s": "dev-master"', $package), $composer);
        }
    }

    public function testDoctrineMapsObjectingEmbeddablesUsedByCartingEntities(): void
    {
        $doctrine = file_get_contents($this->root() . '/config/packages/doctrine.yaml');
        self::assertIsString($doctrine);

        self::assertStringContainsString("vendor/objecting/object/src/Embeddable", $doctrine);
        self::assertStringContainsString("prefix: 'App\\Objecting\\Embeddable'", $doctrine);
    }

    public function testAuditTransitionMigrationCoversCurrentCartLifecycle(): void
    {
        $migration = file_get_contents($this->root() . '/migrations/Version20260914082000.php');
        self::assertIsString($migration);

        self::assertStringContainsString('modified_at = updated_at', $migration);
        self::assertStringContainsString('DROP COLUMN IF EXISTS updated_at', $migration);
        self::assertStringContainsString("'checkout_pending'", $migration);
        self::assertStringContainsString("'merged'", $migration);
        self::assertStringContainsString('downstream_reference', $migration);
        self::assertStringContainsString('accepted_at', $migration);
    }

    /** @return list<\SplFileInfo> */
    private function phpFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && 'php' === $file->getExtension()) {
                $files[] = $file;
            }
        }

        return $files;
    }

    private function root(): string
    {
        return dirname(__DIR__, 3);
    }
}
