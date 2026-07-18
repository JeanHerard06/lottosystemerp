<?php
require_once __DIR__ . '/../auth.php';

$user = mobile_user($pdo);
$userId = (int)$user['id'];
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;

$params = [$userId];
$where = "(user_id = ? OR user_id IS NULL) AND read_at IS NULL";
if ($tenantId) {
    $where .= " AND (tenant_id = ? OR tenant_id IS NULL)";
    $params[] = $tenantId;
} else {
    $where .= " AND tenant_id IS NULL";
}

$readAt = TimeService::sqlNow();
$stmt = $pdo->prepare("UPDATE notifications SET read_at = ? WHERE {$where}");
$stmt->execute(array_merge([$readAt], $params));

mobile_json(['success' => true, 'message' => 'Toutes les notifications sont lues']);
