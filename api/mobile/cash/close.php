<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/cash_sessions.php';
$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { $payload = $_POST; }
$closingAmount = (float)($payload['closing_amount'] ?? 0);
$notes = trim((string)($payload['notes'] ?? ''));
$session = open_cash_session($pdo, (int)$agent['id']);
if (!$session) {
    mobile_json(['success' => false, 'message' => 'Aucune session ouverte.'], 404);
}
$totals = cash_session_totals($pdo, (int)$session['id']);
$expected = cash_expected_amount((float)$session['opening_amount'], $totals);
$difference = $closingAmount - $expected;
try {
    $closedAt = TimeService::sqlNow();
    $stmt = $pdo->prepare('UPDATE cash_sessions SET status="closed", closing_amount=?, expected_amount=?, difference_amount=?, closed_by=?, closed_at=?, notes=CONCAT(COALESCE(notes,""), ?) WHERE id=? AND status="open"');
    $extraNotes = $notes !== '' ? "\nMobile fermeture: " . $notes : '';
    $stmt->execute([$closingAmount, $expected, $difference, (int)$user['id'], $closedAt, $extraNotes, (int)$session['id']]);
    mobile_json(['success' => true, 'message' => 'Session fermée', 'expected_amount' => $expected, 'difference' => $difference]);
} catch (Throwable $e) {
    mobile_json(['success' => false, 'message' => 'Erreur fermeture session: ' . $e->getMessage()], 500);
}
