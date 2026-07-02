<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/fiches.php';

require_permission($pdo, 'fiches.cancel');
require_post();
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM fiches WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$fiche = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$fiche || !can_access_fiche($pdo, $fiche)) {
    http_response_code(404);
    die('Fiche introuvable.');
}
if ($fiche['status'] === 'paid') {
    die('Impossible d\'annuler une fiche déjà payée.');
}
if ($fiche['status'] === 'cancelled') {
    redirect('../../views/fiches/show.php?id=' . $id);
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE fiches SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("INSERT INTO agent_transactions (agent_id, type, amount, description) VALUES (?, 'retrait', ?, ?)");
    $stmt->execute([(int)$fiche['agent_id'], (float)$fiche['total_amount'], 'Annulation fiche ' . $fiche['fiche_code']]);

    audit_log($pdo, current_user_id(), 'CANCEL_FICHE', 'Fiche annulée: ' . $fiche['fiche_code']);
    $pdo->commit();
    redirect('../../views/fiches/show.php?id=' . $id);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die('Erreur: ' . e($e->getMessage()));
}
