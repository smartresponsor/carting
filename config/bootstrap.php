<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

if (!class_exists(Dotenv::class)) {
    return;
}

$projectDir = dirname(__DIR__);
$dotenv = new Dotenv();
$localEnv = $projectDir.'/.env';

if (is_file($localEnv)) {
    $dotenv->bootEnv($localEnv);
}

$databaseUrl = getenv('DATABASE_URL');
$arguments = $_SERVER['argv'] ?? [];
$envOptionIndex = array_search('--env', $arguments, true);
$testEnvironmentRequested = 'test' === ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null)
    || in_array('--env=test', $arguments, true)
    || false !== $envOptionIndex && 'test' === ($arguments[$envOptionIndex + 1] ?? null);
$hostEnv = dirname($projectDir).'/App/.env';

if ($testEnvironmentRequested) {
    if ((false === $databaseUrl || '' === $databaseUrl) && is_file($hostEnv)) {
        $dotenv->bootEnv($hostEnv);
    }

    $hostDatabaseUrl = $_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
    if (is_string($hostDatabaseUrl) && '' !== trim($hostDatabaseUrl)) {
        $testDatabaseUrl = preg_replace('~/[^/?]+(\\?.*)?$~', '/carting_test$1', $hostDatabaseUrl);
        if (is_string($testDatabaseUrl)) {
            putenv('DATABASE_URL='.$testDatabaseUrl);
            $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = $testDatabaseUrl;
            putenv('APP_ENV=test');
            $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
        }
    }
}
