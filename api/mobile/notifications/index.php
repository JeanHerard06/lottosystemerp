<?php
require_once __DIR__ . '/../auth.php';

$user = mobile_user($pdo);
$userId = (int)$user['id'];
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
$unreadOnly = isset($_GET['unread']) && $_GET['unread'] === '1';
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;

$params = [];
$where = "WHERE (user_id = ? OR user_id IS NULL)";
$params[] = $userId;

if ($tenantId) {
    $where .= " AND (tenant_id = ? OR tenant_id IS NULL)";
    $params[] = $tenantId;
} else {
    $where .= " AND tenant_id IS NULL";
}

if ($unreadOnly) {
    $where .= " AND read_at IS NULL";
}

$sql = "
    SELECT id, title, message, type, link_url, read_at, created_at
    FROM notifications
    {$where}
    ORDER BY created_at DESC
    LIMIT {$limit}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "SELECT COUNT(*) FROM notifications {$where} AND read_at IS NULL";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$unreadCount = (int)$countStmt->fetchColumn();

mobile_json([
    'success' => true,
    'data' => [
        'unread_count' => $unreadCount,
        'notifications' => $rows,
    ],
]);
