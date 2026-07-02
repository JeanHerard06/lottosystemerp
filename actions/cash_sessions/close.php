<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/cash_sessions.php';

require_permission($pdo, 'cash_sessions.manage');
require_post();
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$closingAmount = input_money('closing_amount');
$notes = input_string('notes', 1000, false);

$stmt = $pdo->prepare('SELECT * FROM cash_sessions WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($session ?: null, 'session caisse');
if ($session['status'] !== 'open') { die('Cette session n\'est pas ouverte.'); }
if (current_user_role() === 'agent') {
    $agent = current_agent_record($pdo);
    if (!$agent || (int)$session['agent_id'] !== (int)$agent['id']) { http_response_code(403); die('Accès refusé.'); }
}

$totals = cash_session_totals($pdo, $id);
$expected = cash_expected_amount((float)$session['opening_amount'], $totals);
$difference = $closingAmount - $expected;

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE cash_sessions SET status="closed", closing_amount=?, expected_amount=?, difference_amount=?, closed_by=?, closed_at=NOW(), notes=CONCAT(COALESCE(notes,""), ?) WHERE id=?');
    $extraNotes = $notes !== '' ? "\nFermeture: " . $notes : '';
    $stmt->execute([$closingAmount, $expected, $difference, current_user_id(), $extraNotes, $id]);
    audit_log($pdo, current_user_id(), 'CLOSE_CASH_SESSION', 'Session caisse fermée #' . $id . ' expected=' . $expected . ' closing=' . $closingAmount . ' diff=' . $difference);
    $pdo->commit();
    redirect('/views/cash_sessions/show.php?id=' . $id . '&closed=1');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die('Erreur fermeture session: ' . e($e->getMessage()));
}
