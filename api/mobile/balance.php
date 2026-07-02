<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);

$stmt = $pdo->prepare("SELECT type, amount, description, created_at FROM agent_transactions WHERE agent_id=? ORDER BY id DESC LIMIT 30");
$stmt->execute([$agent['id']]);

mobile_json([
    'success' => true,
    'balance' => (float)$agent['balance'],
    'transactions' => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);
