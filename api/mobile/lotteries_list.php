<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../app/Helpers/lotteries.php';

$user = mobile_user($pdo);
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
$params = [];
$where = "WHERE status = 1";
if ($tenantId) {
    $where .= " AND tenant_id = ?";
    $params[] = $tenantId;
}

$stmt = $pdo->prepare("SELECT id, name, draw_time, close_before_minutes, sales_status, auto_close_enabled FROM lotteries {$where} ORDER BY name ASC");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $i => $row) {
    $updated = lottery_auto_close_if_due($pdo, $row);
    if (is_array($updated)) {
        $rows[$i] = $updated;
    }
}

$openOnly = ($_GET['open_only'] ?? '1') !== '0';
if ($openOnly) {
    $rows = array_values(array_filter($rows, static function ($row) {
        return ($row['sales_status'] ?? 'open') === 'open';
    }));
}

mobile_json(['success' => true, 'data' => $rows]);
