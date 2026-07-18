<?php
require_once __DIR__ . '/../auth.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$tenantId = (int)($user['tenant_id'] ?? 0);

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { $payload = $_POST; }

$code = trim((string)($payload['code'] ?? $_GET['code'] ?? ''));
if ($code === '') {
    mobile_json(['success' => false, 'message' => 'Code ticket manquant'], 422);
}

$hasGainTenant = mobile_api_has_column($pdo, 'gains', 'tenant_id');
$hasIsPaid = mobile_api_has_column($pdo, 'gains', 'is_paid');
$hasPaidAt = mobile_api_has_column($pdo, 'gains', 'paid_at');
$hasPaidBy = mobile_api_has_column($pdo, 'gains', 'paid_by');

$stmt = $pdo->prepare("\n    SELECT f.id, f.tenant_id, f.agent_id, f.lottery_id, f.fiche_code, f.total_amount, f.gain_amount, f.status, f.created_at,\n           l.name AS lottery_name\n    FROM fiches f\n    LEFT JOIN lotteries l ON l.id = f.lottery_id\n    WHERE f.fiche_code = ?\n      AND f.tenant_id = ?\n    LIMIT 1\n");
$stmt->execute([$code, $tenantId]);
$fiche = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fiche) {
    mobile_json([
        'success' => true,
        'valid' => false,
        'payable' => false,
        'message' => 'Ticket introuvable ou hors tenant.'
    ]);
}

$paidExpr = $hasIsPaid ? 'COALESCE(g.is_paid,0)' : "CASE WHEN g.status='paid' THEN 1 ELSE 0 END";
$tenantSql = $hasGainTenant ? ' AND (g.tenant_id = ? OR g.tenant_id IS NULL)' : '';
$params = [(int)$fiche['id']];
if ($hasGainTenant) { $params[] = $tenantId; }

$stmt = $pdo->prepare("\n    SELECT g.id, g.amount_played, g.amount_won, g.status, {$paidExpr} AS is_paid,\n           fd.number_played, fd.play_type, fd.amount, t.draw_name, t.draw_date\n    FROM gains g\n    JOIN fiche_details fd ON fd.id = g.fiche_detail_id\n    LEFT JOIN tirages t ON t.id = g.tirage_id\n    WHERE fd.fiche_id = ?\n      AND g.status = 'won'\n      {$tenantSql}\n    ORDER BY g.id ASC\n");
$stmt->execute($params);
$gains = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalWon = 0.0;
$totalPaid = 0.0;
$totalPending = 0.0;
foreach ($gains as &$g) {
    $g['amount_won'] = (float)($g['amount_won'] ?? 0);
    $g['is_paid'] = (int)($g['is_paid'] ?? 0);
    $totalWon += $g['amount_won'];
    if ($g['is_paid'] === 1) { $totalPaid += $g['amount_won']; }
    else { $totalPending += $g['amount_won']; }
}
unset($g);

$payable = count($gains) > 0 && $totalPending > 0;
$message = 'Ticket non gagnant.';
if (count($gains) > 0 && $totalPending <= 0) {
    $message = 'Gain déjà payé.';
} elseif ($payable) {
    $message = 'Gain disponible pour paiement.';
}

mobile_json([
    'success' => true,
    'valid' => true,
    'payable' => $payable,
    'message' => $message,
    'ticket' => $fiche,
    'summary' => [
        'total_won' => $totalWon,
        'total_paid' => $totalPaid,
        'total_pending' => $totalPending,
        'gain_count' => count($gains),
    ],
    'gains' => $gains,
    'schema' => [
        'paid_at' => $hasPaidAt,
        'paid_by' => $hasPaidBy,
    ]
]);
