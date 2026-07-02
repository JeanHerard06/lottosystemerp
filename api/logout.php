<?php
require_once __DIR__ . '/auth.php';
$user = api_user($pdo);
$pdo->prepare('UPDATE users SET api_token = NULL WHERE id = ?')->execute([$user['id']]);
api_response(true, ['message'=>'Déconnecté']);
