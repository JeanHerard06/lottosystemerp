<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../_core/response.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND status=1 LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    api_error('Identifiant ou mot de passe incorrect', 401);
}

$accessToken = bin2hex(random_bytes(32));
$refreshToken = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("INSERT INTO api_tokens (tenant_id, user_id, token_hash, refresh_token_hash, expires_at, refresh_expires_at) VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 2 HOUR), DATE_ADD(NOW(), INTERVAL 30 DAY))");
$stmt->execute([
    $user['tenant_id'] ?? null,
    $user['id'],
    hash('sha256', $accessToken),
    hash('sha256', $refreshToken),
]);

api_success([
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken,
    'token_type' => 'Bearer',
    'expires_in' => 7200,
    'user' => [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'role' => $user['role'],
        'tenant_id' => isset($user['tenant_id']) ? (int)$user['tenant_id'] : null,
    ]
], 'Connexion réussie');
