<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/finance.php';
require_once __DIR__ . '/../../app/Helpers/cash_sessions.php';
require_once __DIR__ . '/../../app/Helpers/gains.php';
require_once __DIR__ . '/../../app/Helpers/fiches.php';

require_permission($pdo, 'gains.pay');
require_post();
verify_csrf();

$gainId = (int)($_POST['gain_id'] ?? 0);
if ($gainId <= 0) {
    die('Gain invalide.');
}

$stmt = $pdo->prepare("SELECT g.*, f.id AS fiche_id, f.agent_id, f.tenant_id
    FROM gains g
    JOIN fiche_details fd ON fd.id = g.fiche_detail_id
    JOIN fiches f ON f.id = fd.fiche_id
    WHERE g.id = ? AND g.status = 'won' LIMIT 1");
$stmt->execute([$gainId]);
$gain = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$gain) {
    die('Gain introuvable.');
}
if (!in_array(current_user_role(), ['admin','super_admin'], true)) {
    $tenantId = current_tenant_safe();
    if ($tenantId && isset($gain['tenant_id']) && (int)$gain['tenant_id'] !== $tenantId) {
        http_response_code(403);
        die('Accès refusé: gain hors tenant.');
    }
}
if ((int)$gain['is_paid'] === 1) {
    redirect('/views/gagnants.php?already_paid=1');
}

$cashSession = require_open_cash_session($pdo, (int)$gain['agent_id']);

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('UPDATE gains SET is_paid = 1, paid_at = NOW(), paid_by = ? WHERE id = ?');
    $stmt->execute([current_user_id(), $gainId]);

    post_agent_transaction($pdo, (int)$gain['agent_id'], 'gain', (float)$gain['amount_won'], 'Paiement gain #' . $gainId, current_user_id(), null, (int)$cashSession['id']);
    update_fiche_status_from_gains($pdo, (int)$gain['fiche_id']);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Erreur paiement gain: ' . e($e->getMessage()));
}

audit_log($pdo, current_user_id(), 'PAY_GAIN', 'Paiement gain #' . $gainId . ' montant=' . $gain['amount_won']);
redirect('/views/gagnants.php?paid=1');
