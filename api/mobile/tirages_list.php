<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);

$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
$lotteryId = !empty($_GET['lottery_id']) ? (int)$_GET['lottery_id'] : null;
$params = [];
$where = [];

if ($tenantId) {
    $where[] = '(tenant_id IS NULL OR tenant_id = ?)';
    $params[] = $tenantId;
}
if ($lotteryId) {
    $where[] = 'lottery_id = ?';
    $params[] = $lotteryId;
}

$sql = "SELECT id, lottery_id, draw_name, first_number, second_number, third_number, draw_date, created_at FROM tirages";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY draw_date DESC, id DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
mobile_json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
