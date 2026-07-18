<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/mobile_agent_financial_engine.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$agentId = (int)$agent['id'];
$tenantId = (int)($user['tenant_id'] ?? 0);
$date = TimeService::normalizeDate($_GET['date'] ?? null);

$summary = mobile_agent_financial_summary($pdo, $user, $agent, 'custom', $date, $date);
$entries = [];
if (mobile_api_has_table($pdo, 'agent_transactions')) {
    $select = ['id', 'amount', 'description', 'created_at'];
    if (mobile_api_has_column($pdo, 'agent_transactions', 'reference_no')) $select[] = 'reference_no';
    $where = ["agent_id = ?", "type = 'commission'", 'created_at BETWEEN ? AND ?'];
    $params = [$agentId, $date . ' 00:00:00', $date . ' 23:59:59'];
    if (mobile_api_has_column($pdo, 'agent_transactions', 'tenant_id') && $tenantId > 0) {
        $where[] = 'tenant_id = ?';
        $params[] = $tenantId;
    }
    if (mobile_api_has_column($pdo, 'agent_transactions', 'status')) {
        $where[] = "status = 'posted'";
    }
    $stmt = $pdo->prepare('SELECT ' . implode(', ', $select) . ' FROM agent_transactions WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC');
    $stmt->execute($params);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

mobile_json([
    'success' => true,
    'message' => 'Détail commission chargé.',
    'data' => [
        'date' => $date,
        'sales' => $summary['sales'],
        'commission' => $summary['commission'],
        'by_game' => $summary['by_game'],
        'entries' => $entries,
        'diagnostics' => $summary['diagnostics'],
    ],
]);
