<?php
require_once __DIR__ . '/../auth.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$tenantId = (int)($user['tenant_id'] ?? 0);
$agentId = (int)$agent['id'];
$limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));

if (!mobile_api_has_table($pdo, 'agent_transactions')) {
    mobile_json(['success' => true, 'data' => ['today_paid_gains' => 0, 'entries' => []]]);
}

$where = "WHERE agent_id = ? AND type = 'gain'";
$params = [$agentId];
if (mobile_api_has_column($pdo, 'agent_transactions', 'tenant_id') && $tenantId > 0) { $where .= ' AND tenant_id = ?'; $params[] = $tenantId; }
if (mobile_api_has_column($pdo, 'agent_transactions', 'status')) { $where .= " AND status = 'posted'"; }

$select = ['id', 'amount', 'description', 'created_at'];
if (mobile_api_has_column($pdo, 'agent_transactions', 'reference_no')) $select[] = 'reference_no';
$stmt = $pdo->prepare('SELECT ' . implode(', ', $select) . " FROM agent_transactions {$where} ORDER BY id DESC LIMIT {$limit}");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM agent_transactions {$where} AND DATE(created_at)=CURDATE()");
$stmt->execute($params);
$today = (float)$stmt->fetchColumn();

mobile_json(['success' => true, 'data' => ['today_paid_gains' => $today, 'entries' => $rows]]);
