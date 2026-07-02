<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/finance.php';

require_permission($pdo, 'transactions.void');
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) die('Transaction introuvable.');

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT * FROM agent_transactions WHERE id=? FOR UPDATE');
    $stmt->execute([$id]);
    $trx = $stmt->fetch();
    if (!$trx) throw new RuntimeException('Transaction introuvable.');
    if ($trx['status'] === 'void') throw new RuntimeException('Transaction déjà annulée.');

    $stmt = $pdo->prepare('UPDATE agent_transactions SET status="void", voided_at=NOW(), voided_by=? WHERE id=?');
    $stmt->execute([current_user_id(), $id]);
    sync_agent_balance($pdo, (int)$trx['agent_id']);
    audit_log($pdo, current_user_id(), 'VOID_TRANSACTION', 'Transaction #' . $id . ' annulée');
    $pdo->commit();
    header('Location: /views/finances/transactions.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die('Erreur annulation: ' . e($e->getMessage()));
}
