<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
function api_response($ok, $data = [], $code = 200){ http_response_code($code); echo json_encode(['success'=>$ok] + $data); exit; }
function api_user(PDO $pdo){
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $token = trim(str_replace('Bearer', '', $auth));
    if (!$token) { api_response(false, ['message'=>'Token manquant'], 401); }
    $stmt = $pdo->prepare('SELECT * FROM users WHERE api_token = ? AND status = 1 LIMIT 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { api_response(false, ['message'=>'Token invalide'], 401); }
    return $user;
}
function api_agent(PDO $pdo, array $user){
    $stmt = $pdo->prepare('SELECT * FROM agents WHERE user_id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
