<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/notifications.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_post();
verify_csrf();
require_permission($pdo, 'lotteries.close');

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$allowed = ['open', 'closed', 'drawn'];
if ($id <= 0 || !in_array($action, $allowed, true)) {
    die('Action lottery invalide.');
}

$stmt = $pdo->prepare('SELECT * FROM lotteries WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$lottery = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($lottery, 'lottery');

try {
    if ($action === 'open') {
        $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='open', closed_at=NULL, closed_by=NULL WHERE id=?");
        $description = 'Lottery rouverte: ' . ($lottery['name'] ?? ('#'.$id));
    } elseif ($action === 'drawn') {
        $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='drawn', closed_at=COALESCE(closed_at, NOW()), closed_by=? WHERE id=?");
        $stmt->execute([current_user_id(), $id]);
        audit_log($pdo, current_user_id(), 'LOTTERY_DRAWN', 'Lottery marquée tirée: ' . ($lottery['name'] ?? ('#'.$id)));
        create_notification($pdo, $lottery['tenant_id'] ?? null, null, 'Lottery tirée', 'La lottery ' . ($lottery['name'] ?? ('#'.$id)) . ' est marquée comme tirée.', 'info', '/views/lotteries/index.php', current_user_id());
        redirect('../../views/lotteries/index.php');
        exit;
    } else {
        $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='closed', closed_at=NOW(), closed_by=? WHERE id=?");
        $stmt->execute([current_user_id(), $id]);
        audit_log($pdo, current_user_id(), 'LOTTERY_CLOSED', 'Lottery fermée manuellement: ' . ($lottery['name'] ?? ('#'.$id)));
        create_notification($pdo, $lottery['tenant_id'] ?? null, null, 'Vente fermée', 'La vente est fermée pour la lottery ' . ($lottery['name'] ?? ('#'.$id)) . '.', 'warning', '/views/lotteries/index.php', current_user_id());
        redirect('../../views/lotteries/index.php');
        exit;
    }

    $stmt->execute([$id]);
    audit_log($pdo, current_user_id(), 'LOTTERY_OPENED', $description);
    create_notification($pdo, $lottery['tenant_id'] ?? null, null, 'Vente réouverte', $description, 'success', '/views/lotteries/index.php', current_user_id());
    redirect('../../views/lotteries/index.php');
} catch (Throwable $e) {
    die('Erreur changement statut lottery: ' . e($e->getMessage()));
}
