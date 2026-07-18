<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Core/Autoload.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';

require_permission($pdo, 'settings.manage');

try {
    $filename = (string)($_GET['file'] ?? '');
    $path = (new BackupService($pdo, dirname(__DIR__, 2)))->resolve($filename);
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
} catch (Throwable $e) {
    http_response_code(404);
    exit('Sauvegarde introuvable.');
}
