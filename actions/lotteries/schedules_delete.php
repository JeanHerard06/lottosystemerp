<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';

require_permission($pdo, 'lottery_schedules.manage');
require_post();
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM lottery_schedules WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($schedule ?: null, 'horaire lottery');

$stmt = $pdo->prepare('DELETE FROM lottery_schedules WHERE id=?');
$stmt->execute([$id]);

if (function_exists('saveLog')) {
    saveLog($pdo, current_user_id(), 'LOTTERY_SCHEDULE_DELETE', 'Horaire lottery supprimé');
}

header('Location: /views/lotteries/schedules.php' . (is_super_admin() ? '?tenant_id=' . (int)$schedule['tenant_id'] : ''));
exit;
