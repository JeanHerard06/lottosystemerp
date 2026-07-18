<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/mobile_agent_financial_engine.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$agentId = (int)$agent['id'];
$tenantId = (int)($user['tenant_id'] ?? 0);
$period = $_GET['period'] ?? 'month';
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$bounds = mobile_financial_period_bounds($period, $from, $to);
$summary = mobile_agent_financial_summary($pdo, $user, $agent, $period, $from, $to);

$days = [];
if (mobile_api_has_table($pdo, 'fiches')) {
    [$scope, $params] = mobile_financial_fiche_scope($pdo, $agentId, $tenantId, $bounds['start'], $bounds['end'], 'f');
    $stmt = $pdo->prepare("SELECT DATE(f.created_at) AS day, COUNT(*) AS fiche_count, COALESCE(SUM(f.total_amount),0) AS sales
        FROM fiches f WHERE {$scope}
        GROUP BY DATE(f.created_at) ORDER BY day DESC");
    $stmt->execute($params);
    $salesDays = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($salesDays as $row) {
        $day = (string)$row['day'];
        $daySummary = mobile_agent_financial_summary($pdo, $user, $agent, 'custom', $day, $day);
        $days[] = [
            'day' => $day,
            'entries' => (int)$row['fiche_count'],
            'sales' => (float)$daySummary['sales'],
            'commission' => (float)$daySummary['commission'],
        ];
    }
}

$transactions = [];
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
    if ($bounds['start'] !== null && $bounds['end'] !== null) {
        $where[] = 'created_at BETWEEN ? AND ?';
        $params[] = $bounds['start'];
        $params[] = $bounds['end'];
    }
    $stmt = $pdo->prepare('SELECT ' . implode(', ', $select) . ' FROM agent_transactions WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC, id DESC LIMIT 100');
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

mobile_json([
    'success' => true,
    'message' => 'Historique commissions chargé.',
    'data' => [
        'period' => $period,
        'total' => $summary['commission'],
        'sales' => $summary['sales'],
        'days' => $days,
        'transactions' => $transactions,
        'diagnostics' => $summary['diagnostics'],
    ],
]);
