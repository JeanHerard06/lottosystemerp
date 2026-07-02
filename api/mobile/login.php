<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$deviceId = trim($_POST['device_id'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 1 LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Identifiant ou mot de passe incorrect']);
    exit;
}

if (!in_array($user['role'], ['agent', 'admin', 'superviseur'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Compte non autorisé sur mobile']);
    exit;
}

$token = bin2hex(random_bytes(40));
$stmt = $pdo->prepare("UPDATE users SET mobile_token = ? WHERE id = ?");
$stmt->execute([$token, $user['id']]);

$stmt = $pdo->prepare("SELECT id, balance, commission, agency_id FROM agents WHERE user_id = ? LIMIT 1");
$stmt->execute([$user['id']]);
$agent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

echo json_encode([
    'success' => true,
    'token' => $token,
    'device_id' => $deviceId,
    'user' => [
        'id' => (int)$user['id'],
        'tenant_id' => $user['tenant_id'] ? (int)$user['tenant_id'] : null,
        'name' => $user['name'],
        'username' => $user['username'],
        'role' => $user['role'],
        'agent' => $agent,
    ],
], JSON_UNESCAPED_UNICODE);
