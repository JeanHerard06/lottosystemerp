<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/gains.php';

require_permission($pdo, 'gains.calculate');
require_post();
verify_csrf();

$tirageId = (int)($_POST['tirage_id'] ?? 0);
if ($tirageId <= 0) {
    die('Tirage invalide.');
}

try {
    $summary = calculate_tirage_gains($pdo, $tirageId);
    $stmt = $pdo->prepare("SELECT lottery_id FROM tirages WHERE id=? LIMIT 1");
    $stmt->execute([$tirageId]);
    $lotteryId = $stmt->fetchColumn();
    if ($lotteryId) {
        $mark = $pdo->prepare("UPDATE lotteries SET sales_status='drawn', closed_at=COALESCE(closed_at, NOW()), closed_by=? WHERE id=?");
        $mark->execute([current_user_id(), (int)$lotteryId]);
    }
    audit_log($pdo, current_user_id(), 'CALCULATE_GAINS', 'Calcul gains tirage #' . $tirageId . ' gagnants=' . $summary['won'] . ' total=' . $summary['total_won']);
    redirect('/views/tirages/show.php?id=' . $tirageId . '&calculated=1');
} catch (Throwable $e) {
    http_response_code(500);
    die('Erreur calcul gains: ' . e($e->getMessage()));
}
