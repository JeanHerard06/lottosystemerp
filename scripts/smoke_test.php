<?php
/**
 * CLI Smoke Test
 * Usage: php scripts/smoke_test.php
 */
$root = dirname(__DIR__);
$errors = [];

$required = [
    'config/database.php',
    'install.php',
    'upgrade.php',
    'views/dashboard.php',
    'actions/fiche_store.php',
    'admin/system/health.php',
];

foreach ($required as $file) {
    if (!file_exists($root . '/' . $file)) {
        $errors[] = "Missing file: {$file}";
    }
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) !== 'php') continue;
    $cmd = 'php -l ' . escapeshellarg($file->getPathname());
    exec($cmd, $out, $code);
    if ($code !== 0) {
        $errors[] = "PHP syntax failed: " . $file->getPathname();
    }
}

if ($errors) {
    echo "SMOKE TEST FAILED\n";
    foreach ($errors as $e) echo "- {$e}\n";
    exit(1);
}

echo "SMOKE TEST PASSED\n";
exit(0);
