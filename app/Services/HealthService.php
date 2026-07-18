<?php

final class HealthService
{
    public function __construct(private PDO $pdo, private string $basePath)
    {
    }

    public function checks(): array
    {
        $checks = [];
        $checks[] = $this->databaseCheck();
        $checks[] = $this->storageCheck();
        $checks[] = $this->phpCheck();
        $checks[] = $this->extensionCheck('pdo_mysql');
        $checks[] = $this->extensionCheck('json');
        $checks[] = $this->timezoneCheck();
        $checks[] = $this->diskCheck();
        $checks[] = $this->cronCheck();

        return $checks;
    }

    public function summary(): array
    {
        $checks = $this->checks();
        $counts = ['healthy' => 0, 'warning' => 0, 'critical' => 0];
        foreach ($checks as $check) {
            $status = $check['status'] ?? 'critical';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        return [
            'status' => $counts['critical'] > 0 ? 'critical' : ($counts['warning'] > 0 ? 'warning' : 'healthy'),
            'counts' => $counts,
            'checks' => $checks,
            'generated_at' => date(DATE_ATOM),
        ];
    }

    private function databaseCheck(): array
    {
        $started = microtime(true);
        try {
            $this->pdo->query('SELECT 1')->fetchColumn();
            $ms = round((microtime(true) - $started) * 1000, 2);
            return $this->check('database', 'Base de données', $ms > 500 ? 'warning' : 'healthy', $ms . ' ms');
        } catch (Throwable $e) {
            return $this->check('database', 'Base de données', 'critical', $e->getMessage());
        }
    }

    private function storageCheck(): array
    {
        $path = $this->basePath . '/storage';
        if (!is_dir($path) && !@mkdir($path, 0775, true)) {
            return $this->check('storage', 'Stockage', 'critical', 'Dossier storage introuvable et non créable');
        }
        return $this->check('storage', 'Stockage', is_writable($path) ? 'healthy' : 'critical', is_writable($path) ? 'Accessible en écriture' : 'Non accessible en écriture');
    }

    private function phpCheck(): array
    {
        $status = version_compare(PHP_VERSION, '8.0.0', '>=') ? 'healthy' : 'critical';
        return $this->check('php', 'PHP', $status, PHP_VERSION);
    }

    private function extensionCheck(string $extension): array
    {
        $ok = extension_loaded($extension);
        return $this->check('ext_' . $extension, 'Extension ' . $extension, $ok ? 'healthy' : 'critical', $ok ? 'Chargée' : 'Manquante');
    }

    private function timezoneCheck(): array
    {
        $timezone = date_default_timezone_get();
        try {
            new DateTimeZone($timezone);
            return $this->check('timezone', 'Fuseau horaire', 'healthy', $timezone);
        } catch (Throwable $e) {
            return $this->check('timezone', 'Fuseau horaire', 'critical', $timezone);
        }
    }

    private function diskCheck(): array
    {
        $free = @disk_free_space($this->basePath);
        $total = @disk_total_space($this->basePath);
        if ($free === false || $total === false || $total <= 0) {
            return $this->check('disk', 'Espace disque', 'warning', 'Indisponible');
        }
        $percent = ($free / $total) * 100;
        $status = $percent < 5 ? 'critical' : ($percent < 15 ? 'warning' : 'healthy');
        return $this->check('disk', 'Espace disque', $status, round($percent, 1) . '% libre');
    }

    private function cronCheck(): array
    {
        try {
            $exists = (bool)$this->pdo->query("SHOW TABLES LIKE 'cron_runs'")->fetchColumn();
            if (!$exists) {
                return $this->check('cron', 'Planificateur', 'warning', 'Table cron_runs absente');
            }
            $last = $this->pdo->query('SELECT status, started_at, finished_at FROM cron_runs ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if (!$last) {
                return $this->check('cron', 'Planificateur', 'warning', 'Aucune exécution enregistrée');
            }
            $status = ($last['status'] ?? '') === 'failed' ? 'critical' : 'healthy';
            return $this->check('cron', 'Planificateur', $status, 'Dernière exécution: ' . ($last['started_at'] ?? 'N/A'));
        } catch (Throwable $e) {
            return $this->check('cron', 'Planificateur', 'warning', $e->getMessage());
        }
    }

    private function check(string $key, string $label, string $status, string $message): array
    {
        return compact('key', 'label', 'status', 'message');
    }
}
