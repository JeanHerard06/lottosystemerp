<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);
$tenantWhere = '';
$params = [];
if (!empty($user['tenant_id'])) {
    $tenantWhere = 'WHERE tenant_id IS NULL OR tenant_id = ?';
    $params[] = $user['tenant_id'];
}
$stmt = $pdo->prepare("SELECT id, draw_name, first_number, second_number, third_number, draw_date, created_at FROM tirages $tenantWhere ORDER BY draw_date DESC, id DESC LIMIT 50");
$stmt->execute($params);
mobile_json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
