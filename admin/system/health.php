<?php
/**
 * Lotto ERP Enterprise - System Health Check
 * Copy to: admin/system/health.php
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: text/html; charset=UTF-8');

function check_status(bool $ok): string {
    return $ok ? '<span style="color:#15803d;font-weight:bold">OK</span>' : '<span style="color:#dc2626;font-weight:bold">FAILED</span>';
}

$checks = [];

$checks[] = ['PHP Version >= 8.0', version_compare(PHP_VERSION, '8.0.0', '>=')];
$checks[] = ['PDO Extension', extension_loaded('pdo')];
$checks[] = ['PDO MySQL Extension', extension_loaded('pdo_mysql')];
$checks[] = ['JSON Extension', extension_loaded('json')];
$checks[] = ['MBString Extension', extension_loaded('mbstring')];
$checks[] = ['File Upload Enabled', (bool) ini_get('file_uploads')];

try {
    $pdo->query('SELECT 1');
    $checks[] = ['Database Connection', true];
} catch (Throwable $e) {
    $checks[] = ['Database Connection', false];
}

$requiredTables = [
    'users', 'roles', 'permissions', 'tenants', 'agencies', 'agents',
    'lotteries', 'tirages', 'fiches', 'fiche_details', 'gains',
    'cash_sessions', 'notifications', 'audit_logs', 'system_settings'
];

foreach ($requiredTables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
        $checks[] = ["Table: {$table}", (bool) $stmt->fetchColumn()];
    } catch (Throwable $e) {
        $checks[] = ["Table: {$table}", false];
    }
}

$writablePaths = [
    __DIR__ . '/../../storage',
    __DIR__ . '/../../storage/logs',
    __DIR__ . '/../../storage/backups',
    __DIR__ . '/../../public/uploads',
];

foreach ($writablePaths as $path) {
    $checks[] = ['Writable: ' . str_replace(dirname(__DIR__, 2), '', $path), is_dir($path) && is_writable($path)];
}

$allOk = !in_array(false, array_column($checks, 1), true);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>System Health - Lotto ERP</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:30px;color:#111827}
        .box{max-width:1000px;margin:auto;background:white;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.08);padding:24px}
        h1{margin-top:0}.summary{padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:bold}
        .ok{background:#dcfce7;color:#166534}.fail{background:#fee2e2;color:#991b1b}
        table{width:100%;border-collapse:collapse}td,th{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left}
        th{background:#f9fafb}.small{color:#6b7280;font-size:13px;margin-top:20px}
    </style>
</head>
<body>
<div class="box">
    <h1>System Health Check</h1>
    <div class="summary <?= $allOk ? 'ok' : 'fail' ?>">
        <?= $allOk ? 'Tout bagay sanble anfòm.' : 'Gen kèk pwen ki bezwen koreksyon.' ?>
    </div>
    <table>
        <thead><tr><th>Check</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($checks as [$label, $ok]): ?>
            <tr><td><?= htmlspecialchars($label) ?></td><td><?= check_status((bool)$ok) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="small">Pou sekirite, limite aksè paj sa a sèlman pou super_admin oswa retire l nan production si ou pa itilize middleware.</p>
</div>
</body>
</html>
