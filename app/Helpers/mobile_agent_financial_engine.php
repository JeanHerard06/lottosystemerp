<?php

declare(strict_types=1);

require_once __DIR__ . '/../Services/TimeService.php';

// Shared schema inspection fallbacks. Mobile API defines these in auth.php;
// web dashboards also reuse this financial engine, so keep the helpers
// available without loading the mobile authentication bootstrap.
if (!function_exists('mobile_api_has_table')) {
    function mobile_api_has_table(PDO $pdo, string $table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) return $cache[$table];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        return $cache[$table] = ((int)$stmt->fetchColumn() > 0);
    }
}

if (!function_exists('mobile_api_has_column')) {
    function mobile_api_has_column(PDO $pdo, string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) return $cache[$key];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        return $cache[$key] = ((int)$stmt->fetchColumn() > 0);
    }
}

/**
 * Single source of truth for Agent Mobile financial KPIs.
 *
 * Existing accounting convention preserved by this project:
 *   balance = sales + commission + deposits - withdrawals - gains_paid.
 *
 * Posted agent_transactions remain authoritative. For legacy fiches that were
 * created before ledger posting was introduced, missing sales/commission values
 * are calculated from fiches and commission rules without double-counting.
 */
function mobile_financial_period_bounds(string $period = 'today', ?string $from = null, ?string $to = null): array
{
    $tz = new DateTimeZone(TimeService::timezone());
    $now = TimeService::now();

    switch ($period) {
        case 'week':
            $start = $now->modify('monday this week')->setTime(0, 0, 0);
            $end = $now->modify('sunday this week')->setTime(23, 59, 59);
            break;
        case 'month':
            $start = $now->modify('first day of this month')->setTime(0, 0, 0);
            $end = $now->modify('last day of this month')->setTime(23, 59, 59);
            break;
        case 'custom':
            $fromDate = TimeService::normalizeDate($from, TimeService::monthStart());
            $toDate = TimeService::normalizeDate($to, TimeService::today());
            $start = new DateTimeImmutable($fromDate . ' 00:00:00', $tz);
            $end = new DateTimeImmutable($toDate . ' 23:59:59', $tz);
            if ($end < $start) {
                [$start, $end] = [$end->setTime(0, 0, 0), $start->setTime(23, 59, 59)];
            }
            break;
        case 'all':
            return ['start' => null, 'end' => null, 'period' => 'all'];
        case 'today':
        default:
            $start = $now->setTime(0, 0, 0);
            $end = $now->setTime(23, 59, 59);
            $period = 'today';
            break;
    }

    return [
        'start' => $start->format('Y-m-d H:i:s'),
        'end' => $end->format('Y-m-d H:i:s'),
        'period' => $period,
    ];
}

function mobile_financial_fiche_scope(PDO $pdo, int $agentId, int $tenantId, ?string $start, ?string $end, string $alias = 'f'): array
{
    $where = ["{$alias}.agent_id = ?"];
    $params = [$agentId];

    if (mobile_api_has_column($pdo, 'fiches', 'tenant_id') && $tenantId > 0) {
        $where[] = "{$alias}.tenant_id = ?";
        $params[] = $tenantId;
    }
    if (mobile_api_has_column($pdo, 'fiches', 'status')) {
        $where[] = "{$alias}.status <> 'cancelled'";
    }
    if ($start !== null && $end !== null) {
        $where[] = "{$alias}.created_at BETWEEN ? AND ?";
        $params[] = $start;
        $params[] = $end;
    }

    return [implode(' AND ', $where), $params];
}

function mobile_financial_sales_by_game(PDO $pdo, array $agent, int $tenantId, ?string $start, ?string $end): array
{
    if (!mobile_api_has_table($pdo, 'fiches') || !mobile_api_has_table($pdo, 'fiche_details')) {
        return [];
    }

    [$scope, $params] = mobile_financial_fiche_scope($pdo, (int)$agent['id'], $tenantId, $start, $end, 'f');
    $stmt = $pdo->prepare("SELECT fd.play_type, COUNT(*) AS play_lines, COALESCE(SUM(fd.amount),0) AS sales_amount
        FROM fiche_details fd
        JOIN fiches f ON f.id = fd.fiche_id
        WHERE {$scope}
        GROUP BY fd.play_type
        ORDER BY fd.play_type");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($rows as &$row) {
        $row['play_lines'] = (int)($row['play_lines'] ?? 0);
        $row['lines'] = $row['play_lines']; // compatibility with older Flutter builds
        $row['sales_amount'] = round((float)($row['sales_amount'] ?? 0), 2);
        $row['commission_rate'] = mobile_financial_commission_rate($pdo, $agent, (string)$row['play_type']);
        $row['commission_amount'] = round($row['sales_amount'] * $row['commission_rate'] / 100, 2);
    }
    unset($row);

    return $rows;
}

function mobile_financial_commission_rate(PDO $pdo, array $agent, string $game): float
{
    if (mobile_api_has_table($pdo, 'commissions')) {
        $stmt = $pdo->prepare('SELECT percentage FROM commissions WHERE agent_id=? AND game_type=? LIMIT 1');
        $stmt->execute([(int)$agent['id'], $game]);
        $value = $stmt->fetchColumn();
        if ($value !== false && $value !== null) {
            return max(0.0, (float)$value);
        }
    }

    $column = $game . '_rate';
    if (array_key_exists($column, $agent)) {
        return max(0.0, (float)$agent[$column]);
    }
    return max(0.0, (float)($agent['commission'] ?? 0));
}

function mobile_financial_calculated_commission(PDO $pdo, array $agent, int $tenantId, ?string $start, ?string $end): float
{
    $total = 0.0;
    foreach (mobile_financial_sales_by_game($pdo, $agent, $tenantId, $start, $end) as $row) {
        $total += (float)$row['commission_amount'];
    }
    return round($total, 2);
}

function mobile_financial_direct_sales(PDO $pdo, array $agent, int $tenantId, ?string $start, ?string $end): array
{
    if (!mobile_api_has_table($pdo, 'fiches')) {
        return ['count' => 0, 'amount' => 0.0];
    }
    [$scope, $params] = mobile_financial_fiche_scope($pdo, (int)$agent['id'], $tenantId, $start, $end, 'f');
    $stmt = $pdo->prepare("SELECT COUNT(*) AS fiche_count, COALESCE(SUM(f.total_amount),0) AS sales_amount FROM fiches f WHERE {$scope}");
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    return [
        'count' => (int)($row['fiche_count'] ?? 0),
        'amount' => round((float)($row['sales_amount'] ?? 0), 2),
    ];
}

function mobile_financial_ledger_totals(PDO $pdo, int $agentId, int $tenantId, ?string $start, ?string $end): array
{
    $result = ['vente'=>0.0, 'commission'=>0.0, 'depot'=>0.0, 'retrait'=>0.0, 'gain'=>0.0, 'entries'=>0];
    if (!mobile_api_has_table($pdo, 'agent_transactions')) {
        return $result;
    }

    $where = ['agent_id = ?'];
    $params = [$agentId];
    if (mobile_api_has_column($pdo, 'agent_transactions', 'tenant_id') && $tenantId > 0) {
        $where[] = 'tenant_id = ?';
        $params[] = $tenantId;
    }
    if (mobile_api_has_column($pdo, 'agent_transactions', 'status')) {
        $where[] = "status = 'posted'";
    }
    if ($start !== null && $end !== null) {
        $where[] = 'created_at BETWEEN ? AND ?';
        $params[] = $start;
        $params[] = $end;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) AS entries,
        COALESCE(SUM(CASE WHEN type='vente' THEN amount ELSE 0 END),0) AS vente,
        COALESCE(SUM(CASE WHEN type='commission' THEN amount ELSE 0 END),0) AS commission,
        COALESCE(SUM(CASE WHEN type='depot' THEN amount ELSE 0 END),0) AS depot,
        COALESCE(SUM(CASE WHEN type='retrait' THEN amount ELSE 0 END),0) AS retrait,
        COALESCE(SUM(CASE WHEN type='gain' THEN amount ELSE 0 END),0) AS gain
        FROM agent_transactions WHERE " . implode(' AND ', $where));
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    foreach (['vente','commission','depot','retrait','gain'] as $key) {
        $result[$key] = round((float)($row[$key] ?? 0), 2);
    }
    $result['entries'] = (int)($row['entries'] ?? 0);
    return $result;
}

function mobile_financial_gains(PDO $pdo, int $agentId, int $tenantId, ?string $start, ?string $end): float
{
    if (!mobile_api_has_table($pdo, 'gains') || !mobile_api_has_table($pdo, 'fiche_details')) {
        return 0.0;
    }
    [$scope, $params] = mobile_financial_fiche_scope($pdo, $agentId, $tenantId, $start, $end, 'f');
    $extra = '';
    if (mobile_api_has_column($pdo, 'gains', 'tenant_id') && $tenantId > 0) {
        $extra .= ' AND (g.tenant_id = ? OR g.tenant_id IS NULL)';
        $params[] = $tenantId;
    }
    if (mobile_api_has_column($pdo, 'gains', 'status')) {
        $extra .= " AND g.status IN ('won','pending','approved')";
    }
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(g.amount_won),0)
        FROM gains g
        JOIN fiche_details fd ON fd.id=g.fiche_detail_id
        JOIN fiches f ON f.id=fd.fiche_id
        WHERE {$scope}{$extra}");
    $stmt->execute($params);
    return round((float)$stmt->fetchColumn(), 2);
}

function mobile_agent_financial_summary(PDO $pdo, array $user, array $agent, string $period = 'today', ?string $from = null, ?string $to = null): array
{
    $agentId = (int)$agent['id'];
    $tenantId = (int)($user['tenant_id'] ?? 0);
    $bounds = mobile_financial_period_bounds($period, $from, $to);
    $start = $bounds['start'];
    $end = $bounds['end'];

    $directSales = mobile_financial_direct_sales($pdo, $agent, $tenantId, $start, $end);
    $ledger = mobile_financial_ledger_totals($pdo, $agentId, $tenantId, $start, $end);
    $calculatedCommission = mobile_financial_calculated_commission($pdo, $agent, $tenantId, $start, $end);

    // Fiches are the source of truth for sales. Commission is calculated from
    // those same fiches and tenant/agent rules so Dashboard, Commission Center
    // and reports always agree, including legacy tickets without ledger rows.
    $sales = $directSales['amount'];
    $commission = $calculatedCommission > 0.00001 ? $calculatedCommission : $ledger['commission'];
    $commissionSource = $calculatedCommission > 0.00001 ? 'calculated_rules' : ($ledger['commission'] > 0.00001 ? 'ledger_fallback' : 'none');
    $salesSource = 'fiches';

    return [
        'period' => $bounds['period'],
        'period_start' => $start,
        'period_end' => $end,
        'fiche_count' => $directSales['count'],
        'sales' => round($sales, 2),
        'commission' => round($commission, 2),
        'gains' => mobile_financial_gains($pdo, $agentId, $tenantId, $start, $end),
        'gains_paid' => round($ledger['gain'], 2),
        'deposits' => round($ledger['depot'], 2),
        'withdrawals' => round($ledger['retrait'], 2),
        'by_game' => mobile_financial_sales_by_game($pdo, $agent, $tenantId, $start, $end),
        'diagnostics' => [
            'sales_source' => $salesSource,
            'commission_source' => $commissionSource,
            'direct_sales' => $directSales['amount'],
            'ledger_sales' => $ledger['vente'],
            'calculated_commission' => $calculatedCommission,
            'ledger_commission' => $ledger['commission'],
            'ledger_entries' => $ledger['entries'],
        ],
    ];
}

function mobile_agent_financial_balance(PDO $pdo, array $user, array $agent): array
{
    $agentId = (int)$agent['id'];
    $tenantId = (int)($user['tenant_id'] ?? 0);

    // Account-level totals remain useful for audit and legacy history.
    $all = mobile_agent_financial_summary($pdo, $user, $agent, 'all');
    $allLedger = mobile_financial_ledger_totals($pdo, $agentId, $tenantId, null, null);

    // Operational position is based on the current open cash session whenever
    // one exists. This avoids mixing old sessions with today's cash.
    $session = null;
    if (mobile_api_has_table($pdo, 'cash_sessions')) {
        $where = ["agent_id = ?", "status = 'open'"];
        $params = [$agentId];
        if (mobile_api_has_column($pdo, 'cash_sessions', 'tenant_id') && $tenantId > 0) {
            $where[] = 'tenant_id = ?';
            $params[] = $tenantId;
        }
        $stmt = $pdo->prepare('SELECT * FROM cash_sessions WHERE ' . implode(' AND ', $where) . ' ORDER BY id DESC LIMIT 1');
        $stmt->execute($params);
        $session = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    $openingCash = 0.0;
    $periodStart = null;
    $periodEnd = null;
    if ($session) {
        $openingCash = round((float)($session['opening_amount'] ?? 0), 2);
        $periodStart = (string)($session['opened_at'] ?? '');
        $periodEnd = TimeService::sqlNow();
    }

    $directSales = mobile_financial_direct_sales($pdo, $agent, $tenantId, $periodStart, $periodEnd);
    $ledger = mobile_financial_ledger_totals($pdo, $agentId, $tenantId, $periodStart, $periodEnd);
    $commission = mobile_financial_calculated_commission($pdo, $agent, $tenantId, $periodStart, $periodEnd);
    if ($commission <= 0.00001 && $ledger['commission'] > 0.00001) {
        $commission = $ledger['commission'];
    }

    $sales = round((float)$directSales['amount'], 2);
    $gainsPaid = round((float)$ledger['gain'], 2);
    $deposits = round((float)$ledger['depot'], 2);
    $withdrawals = round((float)$ledger['retrait'], 2);

    // Cash physically expected in the drawer. Commission is still part of the
    // cash until the settlement/remittance is completed.
    $cashOnHand = $openingCash + $sales + $deposits - $gainsPaid - $withdrawals;

    // Amount the agent owes the tenant/head office after retaining earned
    // commission. Opening float is excluded because it belongs to the session.
    $amountToRemit = $sales + $deposits - $gainsPaid - $withdrawals - $commission;

    // Backward-compatible `balance`: it now has one clear business meaning —
    // the net amount to remit, not sales + commission.
    $balance = $amountToRemit;

    return [
        'balance' => round($balance, 2),
        'amount_to_remit' => round($amountToRemit, 2),
        'cash_on_hand' => round($cashOnHand, 2),
        'commission_earned' => round($commission, 2),
        'opening_cash' => round($openingCash, 2),
        'has_open_session' => $session !== null,
        'cash_session_id' => $session ? (int)$session['id'] : null,
        'stored_balance' => round((float)($agent['balance'] ?? 0), 2),
        'variance' => round((float)($agent['balance'] ?? 0) - $balance, 2),
        'components' => [
            'opening_cash' => round($openingCash, 2),
            'sales' => $sales,
            'commission' => round($commission, 2),
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'gains_paid' => $gainsPaid,
            'cash_on_hand' => round($cashOnHand, 2),
            'amount_to_remit' => round($amountToRemit, 2),
        ],
        'definitions' => [
            'cash_on_hand' => 'Ouverture + ventes + dépôts - gains payés - retraits',
            'commission_earned' => 'Commission acquise selon les règles de jeu',
            'amount_to_remit' => 'Ventes + dépôts - gains payés - retraits - commission',
        ],
        'diagnostics' => [
            'scope' => $session ? 'open_cash_session' : 'all_history',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'all_time_sales' => $all['sales'],
            'all_time_commission' => $all['commission'],
            'all_time_ledger' => $allLedger,
            'summary' => $all['diagnostics'],
        ],
    ];
}
