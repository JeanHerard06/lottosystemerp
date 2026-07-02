<?php
require_once __DIR__ . '/../auth.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);

$stmt = $pdo->prepare("SELECT id, fiche_code, total_amount, gain_amount, status, sync_source, device_id, created_at FROM fiches WHERE agent_id=? ORDER BY id DESC LIMIT 100");
$stmt->execute([$agent['id']]);
mobile_json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
