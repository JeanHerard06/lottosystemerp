<?php
require_once __DIR__ . '/../auth.php';

$user = mobile_user($pdo);
$userId = (int)$user['id'];
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $raw = json_decode(file_get_contents('php://input'), true);
    $id = (int)($raw['id'] ?? 0);
}

if ($id <= 0) {
    mobile_json(['success' => false, 'message' => 'Notification invalide'], 422);
}

$params = [$id, $userId];
$where = "id = ? AND (user_id = ? OR user_id IS NULL)";
if ($tenantId) {
    $where .= " AND (tenant_id = ? OR tenant_id IS NULL)";
    $params[] = $tenantId;
} else {
    $where .= " AND tenant_id IS NULL";
}

$stmt = $pdo->prepare("DELETE FROM notifications WHERE {$where}");
$stmt->execute($params);

mobile_json(['success' => true, 'message' => 'Notification supprimée']);
