<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/finance.php';
require_once __DIR__ . '/../../app/Helpers/cash_sessions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_permission($pdo, 'finances.manage');
require_post();
verify_csrf();

$agentId = (int)($_POST['agent_id'] ?? 0);
$type = $_POST['type'] ?? '';
$amount = (float)($_POST['amount'] ?? 0);
$description = trim($_POST['description'] ?? '');

if ($agentId <= 0 || !in_array($type, ['depot','retrait'], true) || $amount <= 0) {
    die('Données transaction invalides.');
}

try {
    $pdo->beginTransaction();
    $cashSession = open_cash_session($pdo, $agentId);
    $transactionId = post_agent_transaction($pdo, $agentId, $type, $amount, $description, current_user_id(), null, $cashSession ? (int)$cashSession['id'] : null);
    audit_log($pdo, current_user_id(), 'FINANCE_TRANSACTION', strtoupper($type) . ' #' . $transactionId . ' agent=' . $agentId . ' montant=' . $amount);
    $pdo->commit();
    header('Location: /views/finances/transactions.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die('Erreur finance: ' . e($e->getMessage()));
}
