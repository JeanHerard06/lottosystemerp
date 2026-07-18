<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/cash_sessions.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$tenantId = (int)($user['tenant_id'] ?? 0);
$agentId = (int)$agent['id'];

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { $payload = $_POST; }

$code = trim((string)($payload['code'] ?? ''));
if ($code === '') {
    mobile_json(['success' => false, 'message' => 'Code ticket manquant'], 422);
}

$cashSession = open_cash_session($pdo, $agentId);
if (!$cashSession) {
    mobile_json(['success' => false, 'message' => 'Aucune session de caisse ouverte.'], 403);
}

$hasGainTenant = mobile_api_has_column($pdo, 'gains', 'tenant_id');
$hasIsPaid = mobile_api_has_column($pdo, 'gains', 'is_paid');
$hasPaidAt = mobile_api_has_column($pdo, 'gains', 'paid_at');
$hasPaidBy = mobile_api_has_column($pdo, 'gains', 'paid_by');
$hasPaidCashSession = mobile_api_has_column($pdo, 'gains', 'paid_cash_session_id');

$stmt = $pdo->prepare("\n    SELECT f.id, f.tenant_id, f.agent_id, f.fiche_code, f.gain_amount, f.status\n    FROM fiches f\n    WHERE f.fiche_code = ?\n      AND f.tenant_id = ?\n    LIMIT 1\n");
$stmt->execute([$code, $tenantId]);
$fiche = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$fiche) {
    mobile_json(['success' => false, 'message' => 'Ticket introuvable ou hors tenant.'], 404);
}

$paidExpr = $hasIsPaid ? 'COALESCE(g.is_paid,0)' : "CASE WHEN g.status='paid' THEN 1 ELSE 0 END";
$tenantSql = $hasGainTenant ? ' AND (g.tenant_id = ? OR g.tenant_id IS NULL)' : '';
$params = [(int)$fiche['id']];
if ($hasGainTenant) { $params[] = $tenantId; }

$stmt = $pdo->prepare("\n    SELECT g.id, g.amount_won, {$paidExpr} AS is_paid\n    FROM gains g\n    JOIN fiche_details fd ON fd.id = g.fiche_detail_id\n    WHERE fd.fiche_id = ?\n      AND g.status = 'won'\n      {$tenantSql}\n    FOR UPDATE\n");

try {
    $pdo->beginTransaction();
    $stmt->execute($params);
    $gains = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pendingIds = [];
    $amount = 0.0;
    foreach ($gains as $g) {
        if ((int)($g['is_paid'] ?? 0) !== 1) {
            $pendingIds[] = (int)$g['id'];
            $amount += (float)$g['amount_won'];
        }
    }

    if (!$pendingIds || $amount <= 0) {
        $pdo->rollBack();
        mobile_json(['success' => false, 'message' => 'Aucun gain payable ou gain déjà payé.'], 409);
    }

    $set = [];
    if ($hasIsPaid) { $set[] = 'is_paid = 1'; }
    if ($hasPaidAt) { $set[] = 'paid_at = ' . $pdo->quote(TimeService::sqlNow()); }
    if ($hasPaidBy) { $set[] = 'paid_by = ' . (int)$user['id']; }
    if ($hasPaidCashSession) { $set[] = 'paid_cash_session_id = ' . (int)$cashSession['id']; }

    if (!$set) {
        $pdo->rollBack();
        mobile_json(['success' => false, 'message' => 'Schema gains pa sipòte paiement mobile. Ajoute is_paid/paid_at/paid_by.'], 500);
    }

    $placeholders = implode(',', array_fill(0, count($pendingIds), '?'));
    $update = $pdo->prepare('UPDATE gains SET ' . implode(', ', $set) . " WHERE id IN ($placeholders)");
    $update->execute($pendingIds);

    mobile_insert_agent_transaction($pdo, [
        'tenant_id' => $tenantId,
        'agent_id' => $agentId,
        'cash_session_id' => (int)$cashSession['id'],
        'type' => 'gain',
        'amount' => $amount,
        'description' => 'Paiement gain mobile ticket ' . $code,
        'created_by' => (int)$user['id'],
    ]);

    mobile_audit($pdo, $tenantId, (int)$user['id'], 'MOBILE_PAY_GAIN', 'Paiement gain ticket ' . $code . ' montant=' . $amount);

    $pdo->commit();

    mobile_json([
        'success' => true,
        'message' => 'Gain payé avec succès.',
        'data' => [
            'fiche_code' => $code,
            'amount_paid' => $amount,
            'cash_session_id' => (int)$cashSession['id'],
            'paid_gain_ids' => $pendingIds,
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    mobile_json(['success' => false, 'message' => 'Erreur paiement gain: ' . $e->getMessage()], 500);
}

function mobile_insert_agent_transaction(PDO $pdo, array $data): void
{
    $columns = ['agent_id', 'type', 'amount', 'description'];
    $values = [(int)$data['agent_id'], (string)$data['type'], (float)$data['amount'], (string)$data['description']];

    foreach (['tenant_id', 'cash_session_id', 'created_by'] as $col) {
        if (mobile_api_has_column($pdo, 'agent_transactions', $col)) {
            $columns[] = $col;
            $values[] = $data[$col] ?? null;
        }
    }
    if (mobile_api_has_column($pdo, 'agent_transactions', 'reference_no')) {
        $columns[] = 'reference_no';
        $values[] = 'GAIN-' . TimeService::now()->format('YmdHis') . '-' . random_int(100, 999);
    }
    if (mobile_api_has_column($pdo, 'agent_transactions', 'status')) {
        $columns[] = 'status';
        $values[] = 'posted';
    }

    $sql = 'INSERT INTO agent_transactions (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
}

function mobile_audit(PDO $pdo, int $tenantId, int $userId, string $action, string $description): void
{
    try {
        $columns = ['user_id', 'action_type', 'description'];
        $values = [$userId, $action, $description];
        if (mobile_api_has_column($pdo, 'audit_logs', 'tenant_id')) {
            $columns[] = 'tenant_id';
            $values[] = $tenantId;
        }
        $sql = 'INSERT INTO audit_logs (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    } catch (Throwable $e) {
        // Audit should not block payment.
    }
}
