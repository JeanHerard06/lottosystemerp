<?php

final class BackupService
{
    public function __construct(private PDO $pdo, private string $basePath)
    {
    }

    public function createDatabaseBackup(): array
    {
        $directory = $this->backupDirectory();
        $filename = 'database_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
        $path = $directory . '/' . $filename;
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new RuntimeException('Impossible de créer le fichier de sauvegarde.');
        }

        try {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");
            $tables = $this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $safeTable = str_replace('`', '``', (string)$table);
                $create = $this->pdo->query("SHOW CREATE TABLE `{$safeTable}`")->fetch(PDO::FETCH_ASSOC);
                $createSql = $create['Create Table'] ?? array_values($create)[1] ?? null;
                if (!$createSql) {
                    continue;
                }
                fwrite($handle, "DROP TABLE IF EXISTS `{$safeTable}`;\n{$createSql};\n\n");

                $stmt = $this->pdo->query("SELECT * FROM `{$safeTable}`", PDO::FETCH_ASSOC);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $columns = array_map(static fn(string $column): string => '`' . str_replace('`', '``', $column) . '`', array_keys($row));
                    $values = array_map(fn($value): string => $value === null ? 'NULL' : $this->pdo->quote((string)$value), array_values($row));
                    fwrite($handle, "INSERT INTO `{$safeTable}` (" . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ");\n");
                }
                fwrite($handle, "\n");
            }
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } catch (Throwable $e) {
            fclose($handle);
            @unlink($path);
            throw $e;
        }
        fclose($handle);

        return $this->metadata($filename);
    }

    public function list(): array
    {
        $files = glob($this->backupDirectory() . '/*.sql') ?: [];
        usort($files, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
        return array_map(fn(string $path): array => $this->metadata(basename($path)), $files);
    }

    public function resolve(string $filename): string
    {
        $filename = basename($filename);
        if (!preg_match('/^[A-Za-z0-9._-]+\.sql$/', $filename)) {
            throw new InvalidArgumentException('Nom de sauvegarde invalide.');
        }
        $path = $this->backupDirectory() . '/' . $filename;
        if (!is_file($path)) {
            throw new RuntimeException('Sauvegarde introuvable.');
        }
        return $path;
    }

    private function backupDirectory(): string
    {
        $directory = $this->basePath . '/storage/backups';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le dossier de sauvegarde.');
        }
        return $directory;
    }

    private function metadata(string $filename): array
    {
        $path = $this->backupDirectory() . '/' . $filename;
        return [
            'filename' => $filename,
            'size' => is_file($path) ? filesize($path) : 0,
            'created_at' => is_file($path) ? date('Y-m-d H:i:s', filemtime($path)) : null,
            'sha256' => is_file($path) ? hash_file('sha256', $path) : null,
        ];
    }
}
