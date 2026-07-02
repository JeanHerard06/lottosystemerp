<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

function mobile_json($payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
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
