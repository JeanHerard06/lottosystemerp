<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_post();
verify_csrf();
require_permission($pdo, 'lotteries.manage');

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM lotteries WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$lottery = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($lottery, 'lottery');

$stmt = $pdo->prepare('SELECT COUNT(*) FROM tirages WHERE lottery_id=?');
$stmt->execute([$id]);
$tirages = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM fiches WHERE lottery_id=?');
$stmt->execute([$id]);
$fiches = (int)$stmt->fetchColumn();

if ($tirages > 0 || $fiches > 0) {
    die('Suppression impossible: cette lottery contient déjà des tirages ou des fiches. Désactivez-la plutôt.');
}

try {
    $stmt = $pdo->prepare('DELETE FROM lotteries WHERE id=?');
    $stmt->execute([$id]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'DELETE_LOTTERY', 'Lottery supprimée: ' . ($lottery['name'] ?? $id));
    redirect('../../views/lotteries/index.php');
} catch (Throwable $e) {
    die('Erreur suppression lottery: ' . e($e->getMessage()));
}
