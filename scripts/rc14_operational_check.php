<?php
$base = dirname(__DIR__);
$required = [
    'VERSION',
    'app/Services/HealthService.php',
    'app/Services/SystemDiagnosticService.php',
    'app/Services/BackupService.php',
    'app/Services/VersionService.php',
    'views/settings/health.php',
    'views/settings/diagnostics.php',
    'views/settings/backups.php',
    'views/settings/version.php',
    'actions/diagnostics/run.php',
    'actions/backups/create.php',
    'actions/backups/download.php',
];
$failures = [];
foreach ($required as $file) {
    if (!is_file($base . '/' . $file)) {
        $failures[] = 'Missing: ' . $file;
    }
}
$version = is_file($base . '/VERSION') ? trim((string)file_get_contents($base . '/VERSION')) : '';
if ($version !== '1.0.0-rc1.4') {
    $failures[] = 'Unexpected VERSION: ' . $version;
}
if ($failures) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}
echo "RC1.4 operational structure: OK\n";
