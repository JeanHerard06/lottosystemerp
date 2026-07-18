<?php
require_once __DIR__ . '/../auth.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);

$stmt = $pdo->prepare("SELECT f.id, f.fiche_code, f.total_amount, f.gain_amount, f.status, f.sync_source, f.device_id, f.created_at, l.name AS lottery_name FROM fiches f LEFT JOIN lotteries l ON l.id=f.lottery_id WHERE f.agent_id=? ORDER BY f.id DESC LIMIT 100");
$stmt->execute([$agent['id']]);
mobile_json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
