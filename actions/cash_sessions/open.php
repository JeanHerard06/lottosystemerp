<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/fiches.php';
require_once __DIR__ . '/../../app/Helpers/cash_sessions.php';

require_permission($pdo, 'cash_sessions.manage');
require_post();
verify_csrf();

$agentId = (int)($_POST['agent_id'] ?? 0);
$openingAmount = input_money('opening_amount');
$notes = input_string('notes', 1000, false);

if (current_user_role() === 'agent') {
    $agent = current_agent($pdo);
    if (!$agent) { die('Compte agent introuvable.'); }
    $agentId = (int)$agent['id'];
} else {
    $stmt = $pdo->prepare('SELECT * FROM agents WHERE id=? LIMIT 1');
    $stmt->execute([$agentId]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    ensure_record_tenant($agent ?: null, 'agent');
}

if (open_cash_session($pdo, $agentId)) {
    die('Cet agent possède déjà une session de caisse ouverte.');
}

$tenantId = is_super_admin() ? (int)$agent['tenant_id'] : (int)current_tenant_id();
$agencyId = !empty($agent['agency_id']) ? (int)$agent['agency_id'] : null;

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO cash_sessions(tenant_id, agency_id, agent_id, opened_by, opening_amount, notes, status, opened_at) VALUES (?, ?, ?, ?, ?, ?, "open", NOW())');
    $stmt->execute([$tenantId, $agencyId, $agentId, current_user_id(), $openingAmount, $notes]);
    $sessionId = (int)$pdo->lastInsertId();
    audit_log($pdo, current_user_id(), 'OPEN_CASH_SESSION', 'Session caisse ouverte #' . $sessionId . ' agent=' . $agentId . ' montant=' . $openingAmount);
    $pdo->commit();
    redirect('/views/cash_sessions/show.php?id=' . $sessionId);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die('Erreur ouverture session: ' . e($e->getMessage()));
}
