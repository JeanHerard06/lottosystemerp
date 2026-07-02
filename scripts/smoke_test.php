<?php
/**
 * Basic smoke test placeholder for Lotto ERP Enterprise.
 * Run: php scripts/smoke_test.php
 */

$required = [
    'config/database.php',
    'install.php',
    'upgrade.php',
    'views/dashboard.php',
    'actions/fiche_store.php',
];

$root = dirname(__DIR__);
$failed = false;

foreach ($required as $file) {
    $path = $root . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($path)) {
        echo "[FAIL] Missing: {$file}\n";
        $failed = true;
    } else {
        echo "[OK] {$file}\n";
    }
}

exit($failed ? 1 : 0);
