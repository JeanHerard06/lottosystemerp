<?php
require_once __DIR__ . '/../app/Helpers/env.php';

$appTimezone = (string)env_value('APP_TIMEZONE', 'America/Port-au-Prince');
try { new DateTimeZone($appTimezone); } catch (Throwable $e) { $appTimezone = 'America/Port-au-Prince'; }
date_default_timezone_set($appTimezone);

$host = (string)env_value('DB_HOST', 'localhost');
$port = (string)env_value('DB_PORT', '3306');
$dbname = (string)env_value('DB_DATABASE', 'lotto_system');
$user = (string)env_value('DB_USERNAME', 'root');
$pass = (string)env_value('DB_PASSWORD', '');
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('Erreur connexion base de données: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
