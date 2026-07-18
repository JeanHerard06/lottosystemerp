<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { $payload = $_POST; }
$code = trim((string)($payload['code'] ?? ''));
$notes = trim((string)($payload['notes'] ?? ''));
if ($code === '') {
    mobile_json(['success' => false, 'message' => 'Code ticket manquant'], 422);
}
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
$sql = "SELECT id, tenant_id, fiche_code, total_amount, gain_amount, status FROM fiches WHERE fiche_code = ?";
$params = [$code];
if ($tenantId) { $sql .= " AND tenant_id = ?"; $params[] = $tenantId; }
$sql .= " LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ticket) {
    mobile_json(['success' => false, 'message' => 'Ticket introuvable ou hors tenant'], 404);
}
$gainAmount = (float)($ticket['gain_amount'] ?? 0);
if ($gainAmount <= 0 || !in_array($ticket['status'], ['won','pending','paid'], true)) {
    mobile_json(['success' => false, 'message' => 'Ce ticket ne contient pas de gain disponible.'], 422);
}
try {
    $cols = $pdo->query("SHOW COLUMNS FROM ticket_claims")->fetchAll(PDO::FETCH_COLUMN);
    $hasCustomerTicketId = in_array('customer_ticket_id', $cols, true);
    $hasFicheId = in_array('fiche_id', $cols, true);
    $hasTicketId = in_array('ticket_id', $cols, true);
    $amountCol = in_array('claim_amount', $cols, true) ? 'claim_amount' : 'amount';
    $notesCol = in_array('comment', $cols, true) ? 'comment' : 'notes';

    $dupWhere = [];
    $dupParams = [];
    if ($hasTicketId) { $dupWhere[] = 'ticket_id = ?'; $dupParams[] = (int)$ticket['id']; }
    if ($hasFicheId) { $dupWhere[] = 'fiche_id = ?'; $dupParams[] = (int)$ticket['id']; }
    if ($dupWhere) {
        $dup = $pdo->prepare("SELECT id,status FROM ticket_claims WHERE tenant_id = ? AND status IN ('pending','approved','paid') AND (" . implode(' OR ', $dupWhere) . ") LIMIT 1");
        $dup->execute(array_merge([(int)$ticket['tenant_id']], $dupParams));
        $existing = $dup->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            mobile_json(['success' => false, 'message' => 'Une claim existe déjà pour ce ticket.', 'claim' => $existing], 409);
        }
    }

    $fields = ['tenant_id', $amountCol, 'status', $notesCol, 'claimed_by'];
    $values = [(int)$ticket['tenant_id'], $gainAmount, 'pending', $notes, (int)$user['id']];
    if ($hasTicketId) { $fields[] = 'ticket_id'; $values[] = (int)$ticket['id']; }
    if ($hasFicheId) { $fields[] = 'fiche_id'; $values[] = (int)$ticket['id']; }
    if ($hasCustomerTicketId) { $fields[] = 'customer_ticket_id'; $values[] = 0; }
    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $stmt = $pdo->prepare('INSERT INTO ticket_claims (' . implode(',', $fields) . ') VALUES (' . $placeholders . ')');
    $stmt->execute($values);
    $claimId = (int)$pdo->lastInsertId();
    mobile_json(['success' => true, 'message' => 'Claim soumise', 'claim' => ['id' => $claimId, 'status' => 'pending', 'amount' => $gainAmount]]);
} catch (Throwable $e) {
    mobile_json(['success' => false, 'message' => 'Erreur claim: ' . $e->getMessage()], 500);
}
