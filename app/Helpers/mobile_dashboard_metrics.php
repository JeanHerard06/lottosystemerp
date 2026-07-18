<?php

require_once __DIR__ . '/../Services/TimeService.php';
require_once __DIR__ . '/cash_sessions.php';
require_once __DIR__ . '/mobile_agent_financial_engine.php';

/**
 * Centralised, auditable metrics for the Agent Mobile dashboard.
 */
function mobile_agent_dashboard_metrics(PDO $pdo, array $user, array $agent): array
{
    $today = mobile_agent_financial_summary($pdo, $user, $agent, 'today');
    $balance = mobile_agent_financial_balance($pdo, $user, $agent);

    $cashSession = open_cash_session($pdo, (int)$agent['id']);
    $cashExpected = null;
    $cashSessionId = null;
    if ($cashSession) {
        $cashSessionId = (int)$cashSession['id'];
        $sessionTotals = cash_session_totals($pdo, $cashSessionId);
        $cashExpected = cash_expected_amount((float)$cashSession['opening_amount'], $sessionTotals);
    }

    return [
        'today_fiches' => (int)$today['fiche_count'],
        'today_sales' => (float)$today['sales'],
        'today_gains' => (float)$today['gains'],
        'today_gains_paid' => (float)$today['gains_paid'],
        'today_commission' => (float)$today['commission'],
        'balance' => (float)$balance['balance'], // backward compatibility: amount_to_remit
        'amount_to_remit' => (float)$balance['amount_to_remit'],
        'cash_on_hand' => (float)$balance['cash_on_hand'],
        'commission_earned' => (float)$balance['commission_earned'],
        'opening_cash' => (float)$balance['opening_cash'],
        'unread_notifications' => mobile_dashboard_unread_notifications($pdo, (int)$user['id'], (int)($user['tenant_id'] ?? 0)),
        'cash_session_id' => $cashSessionId,
        'cash_expected' => $cashExpected === null ? null : round($cashExpected, 2),
        'diagnostics' => [
            'timezone' => TimeService::timezone(),
            'period_start' => $today['period_start'],
            'period_end' => $today['period_end'],
            'sales_source' => $today['diagnostics']['sales_source'],
            'commission_source' => $today['diagnostics']['commission_source'],
            'commission_posted' => $today['diagnostics']['ledger_commission'],
            'commission_calculated' => $today['diagnostics']['calculated_commission'],
            'balance_source' => 'amount_to_remit',
            'cash_position_scope' => $balance['diagnostics']['scope'] ?? null,
            'stored_balance' => $balance['stored_balance'],
            'computed_balance' => $balance['balance'],
            'balance_variance' => $balance['variance'],
            'ledger' => $balance['components'],
        ],
    ];
}

function mobile_dashboard_unread_notifications(PDO $pdo, int $userId, int $tenantId): int
{
    if (!mobile_api_has_table($pdo, 'notifications')) return 0;
    $where = '(user_id = ? OR user_id IS NULL)';
    $params = [$userId];
    if (mobile_api_has_column($pdo, 'notifications', 'tenant_id') && $tenantId > 0) {
        $where .= ' AND (tenant_id = ? OR tenant_id IS NULL)';
        $params[] = $tenantId;
    }
    if (mobile_api_has_column($pdo, 'notifications', 'read_at')) {
        $where .= ' AND read_at IS NULL';
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE {$where}");
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}
