<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/mobile_agent_financial_engine.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$agentId = (int)$agent['id'];
$tenantId = (int)($user['tenant_id'] ?? 0);

$today = mobile_agent_financial_summary($pdo, $user, $agent, 'today');
$week = mobile_agent_financial_summary($pdo, $user, $agent, 'week');
$month = mobile_agent_financial_summary($pdo, $user, $agent, 'month');
$balance = mobile_agent_financial_balance($pdo, $user, $agent);

$recent = [];
if (mobile_api_has_table($pdo, 'agent_transactions')) {
    $select = ['id', 'amount', 'description', 'created_at'];
    if (mobile_api_has_column($pdo, 'agent_transactions', 'reference_no')) $select[] = 'reference_no';
    $where = ["agent_id = ?", "type = 'commission'"];
    $params = [$agentId];
    if (mobile_api_has_column($pdo, 'agent_transactions', 'tenant_id') && $tenantId > 0) {
        $where[] = 'tenant_id = ?';
        $params[] = $tenantId;
    }
    if (mobile_api_has_column($pdo, 'agent_transactions', 'status')) {
        $where[] = "status = 'posted'";
    }
    $stmt = $pdo->prepare('SELECT ' . implode(', ', $select) . ' FROM agent_transactions WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC LIMIT 10');
    $stmt->execute($params);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

mobile_json([
    'success' => true,
    'message' => 'Résumé commissions chargé.',
    'data' => [
        'today_sales' => $today['sales'],
        'today_commission' => $today['commission'],
        'week_sales' => $week['sales'],
        'week_commission' => $week['commission'],
        'month_sales' => $month['sales'],
        'month_commission' => $month['commission'],
        'today_gains_paid' => $today['gains_paid'],
        'balance' => $balance['balance'],
        'recent' => $recent,
        'diagnostics' => [
            'today' => $today['diagnostics'],
            'week' => $week['diagnostics'],
            'month' => $month['diagnostics'],
            'balance' => $balance,
        ],
    ],
]);
