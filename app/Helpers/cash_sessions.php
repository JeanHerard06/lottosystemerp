<?php

function open_cash_session(PDO $pdo, int $agentId): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM cash_sessions WHERE agent_id=? AND status='open' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$agentId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function require_open_cash_session(PDO $pdo, int $agentId): array
{
    $session = open_cash_session($pdo, $agentId);
    if (!$session) {
        http_response_code(403);
        die('Aucune session de caisse ouverte. Ouvrez une session avant cette opération.');
    }
    return $session;
}

function cash_session_totals(PDO $pdo, int $sessionId): array
{
    $stmt = $pdo->prepare("\n        SELECT\n            COALESCE(SUM(CASE WHEN type='vente' THEN amount ELSE 0 END),0) AS sales,\n            COALESCE(SUM(CASE WHEN type='gain' THEN amount ELSE 0 END),0) AS paid_gains,\n            COALESCE(SUM(CASE WHEN type='depot' THEN amount ELSE 0 END),0) AS deposits,\n            COALESCE(SUM(CASE WHEN type='retrait' THEN amount ELSE 0 END),0) AS withdrawals,\n            COALESCE(SUM(CASE WHEN type='commission' THEN amount ELSE 0 END),0) AS commissions\n        FROM agent_transactions\n        WHERE cash_session_id=? AND status='posted'\n    ");
    $stmt->execute([$sessionId]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    foreach (['sales','paid_gains','deposits','withdrawals','commissions'] as $key) {
        $totals[$key] = (float)($totals[$key] ?? 0);
    }
    return $totals;
}

function cash_expected_amount(float $openingAmount, array $totals): float
{
    return $openingAmount
        + (float)($totals['sales'] ?? 0)
        + (float)($totals['deposits'] ?? 0)
        - (float)($totals['paid_gains'] ?? 0)
        - (float)($totals['withdrawals'] ?? 0);
}

function session_visible_clause(PDO $pdo, string $csAlias = 'cs', string $aAlias = 'a', string $prefix = 'WHERE'): array
{
    $clauses = [];
    $params = [];
    if (!is_super_admin()) {
        $clauses[] = "$csAlias.tenant_id = ?";
        $params[] = current_tenant_id();
        $role = current_user_role();
        if ($role === 'agent') {
            $agent = current_agent_record($pdo);
            $clauses[] = "$csAlias.agent_id = ?";
            $params[] = $agent ? (int)$agent['id'] : 0;
        } elseif ($role === 'superviseur') {
            $agencyId = scoped_agency_id($pdo);
            if ($agencyId) {
                $clauses[] = "$csAlias.agency_id = ?";
                $params[] = $agencyId;
            }
        }
    }
    if (!$clauses) { return ['', []]; }
    return [' ' . $prefix . ' ' . implode(' AND ', $clauses) . ' ', $params];
}
