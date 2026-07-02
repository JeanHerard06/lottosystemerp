<?php
$checks = [];
function add_check(&$checks, $name, $ok, $message = '') {
    $checks[] = ['name' => $name, 'status' => $ok ? 'OK' : 'FAIL', 'message' => $message];
}

add_check($checks, 'PHP >= 8.1', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION);
foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'fileinfo'] as $ext) {
    add_check($checks, "Extension {$ext}", extension_loaded($ext));
}
foreach (['storage', 'public/uploads', 'backups'] as $dir) {
    $path = __DIR__ . '/../' . $dir;
    add_check($checks, "Writable {$dir}", is_dir($path) && is_writable($path));
}

header('Content-Type: application/json');
echo json_encode(['checked_at' => date('c'), 'checks' => $checks], JSON_PRETTY_PRINT);
