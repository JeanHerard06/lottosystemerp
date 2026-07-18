<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
try {
    $cols = $pdo->query("SHOW COLUMNS FROM ticket_claims")->fetchAll(PDO::FETCH_COLUMN);
    $ticketJoin = in_array('ticket_id', $cols, true) ? 'tc.ticket_id' : (in_array('fiche_id', $cols, true) ? 'tc.fiche_id' : 'NULL');
    $amount = in_array('claim_amount', $cols, true) ? 'tc.claim_amount' : 'tc.amount';
    $comment = in_array('comment', $cols, true) ? 'tc.comment' : 'tc.notes';
    $sql = "SELECT tc.id, tc.status, {$amount} AS amount, {$comment} AS notes, tc.created_at, f.fiche_code FROM ticket_claims tc LEFT JOIN fiches f ON f.id = {$ticketJoin}";
    $params = [];
    if ($tenantId) { $sql .= " WHERE tc.tenant_id = ?"; $params[] = $tenantId; }
    $sql .= " ORDER BY tc.id DESC LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    mobile_json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    mobile_json(['success' => false, 'message' => 'Erreur liste claims: ' . $e->getMessage()], 500);
}
