<?php
// Simple backup helper. Configure credentials through environment variables in production.
$db = getenv('DB_DATABASE') ?: 'lotto_system';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '1qaz';
$host = getenv('DB_HOST') ?: '127.0.0.1';
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0775, true);
}
$file = $backupDir . '/db_' . date('Ymd_His') . '.sql';
$cmd = sprintf(
    'mysqldump -h%s -u%s %s %s > %s',
    escapeshellarg($host),
    escapeshellarg($user),
    $pass !== '' ? '-p' . escapeshellarg($pass) : '',
    escapeshellarg($db),
    escapeshellarg($file)
);
exec($cmd, $output, $code);
if ($code !== 0) {
    fwrite(STDERR, "Backup failed\n");
    exit(1);
}
echo "Backup created: {$file}\n";
