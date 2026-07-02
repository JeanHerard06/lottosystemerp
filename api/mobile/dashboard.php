<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM fiches WHERE agent_id = ? AND DATE(created_at)=CURDATE()");
$stmt->execute([$agent['id']]);
$todayFiches = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM fiches WHERE agent_id = ? AND DATE(created_at)=CURDATE()");
$stmt->execute([$agent['id']]);
$todaySales = (float)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(gain_amount),0) FROM fiches WHERE agent_id = ? AND DATE(created_at)=CURDATE()");
$stmt->execute([$agent['id']]);
$todayGains = (float)$stmt->fetchColumn();

mobile_json([
    'success' => true,
    'data' => [
        'today_fiches' => $todayFiches,
        'today_sales' => $todaySales,
        'today_gains' => $todayGains,
        'balance' => (float)$agent['balance'],
    ]
]);
