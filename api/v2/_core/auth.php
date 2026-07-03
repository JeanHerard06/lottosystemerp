<?php
require_once __DIR__ . '/response.php';

function api_bearer_token(): ?string {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (stripos($auth, 'Bearer ') === 0) {
        return trim(substr($auth, 7));
    }
    return null;
}

function require_api_auth(PDO $pdo): array {
    $token = api_bearer_token();
    if (!$token) {
        api_error('Token manquant', 401);
    }

    $hash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT t.*, u.name, u.role, u.status FROM api_tokens t JOIN users u ON u.id=t.user_id WHERE t.token_hash=? AND t.revoked_at IS NULL AND t.expires_at > NOW() LIMIT 1");
    $stmt->execute([$hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int)$row['status'] !== 1) {
        api_error('Token invalide ou expiré', 401);
    }

    return $row;
}
