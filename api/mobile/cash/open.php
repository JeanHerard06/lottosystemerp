<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/cash_sessions.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { $payload = $_POST; }
$openingAmount = (float)($payload['opening_amount'] ?? 0);
$notes = trim((string)($payload['notes'] ?? ''));
if (open_cash_session($pdo, (int)$agent['id'])) {
    mobile_json(['success' => false, 'message' => 'Une session est déjà ouverte.'], 422);
}
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
$agencyId = !empty($agent['agency_id']) ? (int)$agent['agency_id'] : null;
try {
    $openedAt = TimeService::sqlNow();
    $stmt = $pdo->prepare('INSERT INTO cash_sessions(tenant_id, agency_id, agent_id, opened_by, opening_amount, notes, status, opened_at) VALUES (?, ?, ?, ?, ?, ?, "open", ?)');
    $stmt->execute([$tenantId, $agencyId, (int)$agent['id'], (int)$user['id'], $openingAmount, $notes, $openedAt]);
    $id = (int)$pdo->lastInsertId();
    mobile_json(['success' => true, 'message' => 'Session ouverte', 'session_id' => $id]);
} catch (Throwable $e) {
    mobile_json(['success' => false, 'message' => 'Erreur ouverture session: ' . $e->getMessage()], 500);
}
