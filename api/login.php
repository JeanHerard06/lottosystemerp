<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND status = 1 LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || !password_verify($password, $user['password'])) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Identifiant ou mot de passe incorrect']); exit; }
$token = bin2hex(random_bytes(32));
$pdo->prepare('UPDATE users SET api_token = ? WHERE id = ?')->execute([$token, $user['id']]);
echo json_encode(['success'=>true,'token'=>$token,'user'=>['id'=>(int)$user['id'],'name'=>$user['name'],'role'=>$user['role']]]);
