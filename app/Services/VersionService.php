<?php

final class VersionService
{
    public function __construct(private string $basePath)
    {
    }

    public function information(): array
    {
        $versionFile = $this->basePath . '/VERSION';
        $version = is_file($versionFile) ? trim((string)file_get_contents($versionFile)) : 'unknown';
        $migrationFiles = glob($this->basePath . '/database/migrations/*.sql') ?: [];
        sort($migrationFiles);
        return [
            'application_version' => $version,
            'php_version' => PHP_VERSION,
            'environment' => (string)(getenv('APP_ENV') ?: 'production'),
            'migration_count' => count($migrationFiles),
            'latest_migration' => $migrationFiles ? basename(end($migrationFiles)) : null,
        ];
    }
}
