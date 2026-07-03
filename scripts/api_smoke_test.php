<?php
$required = [
    'api/v2/_core/response.php',
    'api/v2/_core/auth.php',
    'api/v2/auth/login.php',
    'api/v2/auth/me.php',
    'database/migrations/026_api_platform_v2.sql',
    'docs/API_PLATFORM_V2.md',
];

$root = dirname(__DIR__);
$missing = [];
foreach ($required as $file) {
    if (!file_exists($root . '/' . $file)) {
        $missing[] = $file;
    }
}

if ($missing) {
    echo "Missing files:\n" . implode("\n", $missing) . "\n";
    exit(1);
}

echo "API Platform v2 smoke test OK\n";
