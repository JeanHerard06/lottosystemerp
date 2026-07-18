<?php

final class SystemDiagnosticService
{
    public function __construct(private PDO $pdo, private string $basePath)
    {
    }

    public function run(): array
    {
        $health = (new HealthService($this->pdo, $this->basePath))->summary();
        $tests = $health['checks'];
        $tests[] = $this->tableTest('users');
        $tests[] = $this->tableTest('tenants');
        $tests[] = $this->tableTest('lotteries');
        $tests[] = $this->tableTest('fiches');
        $tests[] = $this->tableTest('audit_logs', false);
        $tests[] = $this->migrationTest();

        $critical = count(array_filter($tests, fn(array $test): bool => ($test['status'] ?? '') === 'critical'));
        $warning = count(array_filter($tests, fn(array $test): bool => ($test['status'] ?? '') === 'warning'));

        return [
            'status' => $critical > 0 ? 'critical' : ($warning > 0 ? 'warning' : 'healthy'),
            'generated_at' => date(DATE_ATOM),
            'php_version' => PHP_VERSION,
            'timezone' => date_default_timezone_get(),
            'tests' => $tests,
        ];
    }

    public function save(array $report): string
    {
        $directory = $this->basePath . '/storage/diagnostics';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le dossier diagnostics.');
        }
        $filename = 'diagnostic_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.json';
        $path = $directory . '/' . $filename;
        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json, LOCK_EX) === false) {
            throw new RuntimeException('Impossible d’enregistrer le rapport diagnostic.');
        }
        return $filename;
    }

    private function tableTest(string $table, bool $required = true): array
    {
        try {
            $stmt = $this->pdo->prepare('SHOW TABLES LIKE ?');
            $stmt->execute([$table]);
            $exists = (bool)$stmt->fetchColumn();
            return [
                'key' => 'table_' . $table,
                'label' => 'Table ' . $table,
                'status' => $exists ? 'healthy' : ($required ? 'critical' : 'warning'),
                'message' => $exists ? 'Présente' : 'Absente',
            ];
        } catch (Throwable $e) {
            return ['key' => 'table_' . $table, 'label' => 'Table ' . $table, 'status' => 'critical', 'message' => $e->getMessage()];
        }
    }

    private function migrationTest(): array
    {
        $path = $this->basePath . '/database/migrations';
        $count = is_dir($path) ? count(glob($path . '/*.sql') ?: []) : 0;
        return [
            'key' => 'migrations',
            'label' => 'Migrations',
            'status' => $count > 0 ? 'healthy' : 'warning',
            'message' => $count . ' fichier(s) SQL détecté(s)',
        ];
    }
}
