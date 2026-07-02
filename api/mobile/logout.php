<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);
$stmt = $pdo->prepare("UPDATE users SET mobile_token = NULL WHERE id = ?");
$stmt->execute([$user['id']]);
mobile_json(['success' => true, 'message' => 'Déconnecté']);
