<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../app/Helpers/mobile_dashboard_metrics.php';
require_once __DIR__ . '/../../app/Helpers/mobile_agent_financial_engine.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$agentId = (int)$agent['id'];
$tenantId = (int)($user['tenant_id'] ?? 0);

try {
    $metrics = mobile_agent_dashboard_metrics($pdo, $user, $agent);
    $balance = mobile_agent_financial_balance($pdo, $user, $agent);
    $transactions = [];
    if (mobile_api_has_table($pdo, 'agent_transactions')) {
        $select = ['id','type','amount','description','created_at'];
        if (mobile_api_has_column($pdo, 'agent_transactions', 'reference_no')) $select[] = 'reference_no';
        if (mobile_api_has_column($pdo, 'agent_transactions', 'status')) $select[] = 'status';
        $where = ['agent_id = ?'];
        $params = [$agentId];
        if (mobile_api_has_column($pdo, 'agent_transactions', 'tenant_id') && $tenantId > 0) {
            $where[] = 'tenant_id = ?';
            $params[] = $tenantId;
        }
        if (mobile_api_has_column($pdo, 'agent_transactions', 'status')) {
            $where[] = "status='posted'";
        }
        $stmt = $pdo->prepare('SELECT ' . implode(',', $select) . ' FROM agent_transactions WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC LIMIT 50');
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    mobile_json([
        'success' => true,
        'message' => 'Balance chargée.',
        'data' => [
            'balance' => $balance['balance'],
            'amount_to_remit' => $balance['amount_to_remit'],
            'cash_on_hand' => $balance['cash_on_hand'],
            'commission_earned' => $balance['commission_earned'],
            'opening_cash' => $balance['opening_cash'],
            'has_open_session' => $balance['has_open_session'],
            'cash_session_id' => $balance['cash_session_id'],
            'stored_balance' => $balance['stored_balance'],
            'variance' => $balance['variance'],
            'components' => $balance['components'],
            'cash_expected' => $metrics['cash_expected'],
            'transactions' => $transactions,
            'definitions' => $balance['definitions'],
            'diagnostics' => $balance['diagnostics'],
        ],
        'balance' => $balance['balance'],
        'transactions' => $transactions,
    ]);
} catch (Throwable $e) {
    mobile_json(['success'=>false, 'message'=>'Erreur balance: '.$e->getMessage(), 'data'=>null], 500);
}
