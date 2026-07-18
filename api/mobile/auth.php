<?php
// Mobile API bootstrap. Keep every response JSON-only, even when PHP notices happen.
if (!ob_get_level()) {
    ob_start();
}
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Services/TimeService.php';

function mobile_json(array $payload, int $code = 200): void {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!array_key_exists('success', $payload)) {
        $payload['success'] = $code < 400;
    }
    $payload['meta'] = array_merge($payload['meta'] ?? [], TimeService::meta());
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

set_exception_handler(function (Throwable $e): void {
    mobile_json(['success' => false, 'message' => 'Erreur serveur mobile: ' . $e->getMessage()], 500);
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function mobile_api_has_table(PDO $pdo, string $table): bool {
    static $cache = [];
    $key = $table;
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $stmt->execute([$table]);
    return $cache[$key] = ((int)$stmt->fetchColumn() > 0);
}

function mobile_api_has_column(PDO $pdo, string $table, string $column): bool {
    static $cache = [];
    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return $cache[$key] = ((int)$stmt->fetchColumn() > 0);
}

function mobile_api_columns(PDO $pdo, string $table): array {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];
    $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $stmt->execute([$table]);
    return $cache[$table] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}

function mobile_user(PDO $pdo): array {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = trim(str_replace('Bearer', '', $auth));

    if ($token === '') {
        mobile_json(['success' => false, 'message' => 'Token manquant'], 401);
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE mobile_token = ? AND status = 1 LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        mobile_json(['success' => false, 'message' => 'Token invalide'], 401);
    }

    TimeService::boot($pdo, !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null);
    return $user;
}

function mobile_agent(PDO $pdo, int $userId): array {
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agent) {
        mobile_json(['success' => false, 'message' => 'Compte agent introuvable'], 403);
    }

    return $agent;
}
