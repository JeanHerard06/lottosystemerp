<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';

require_permission($pdo, 'lottery_schedules.manage');
csrf_verify();

$lotteryId = (int)($_POST['lottery_id'] ?? 0);
$drawTime = trim((string)($_POST['draw_time'] ?? ''));
$closeBefore = max(0, (int)($_POST['close_before_minutes'] ?? 10));
$day = ($_POST['day_of_week'] ?? '') === '' ? null : (int)$_POST['day_of_week'];

if ($lotteryId <= 0 || !preg_match('/^\d{2}:\d{2}$/', $drawTime)) {
    http_response_code(422);
    die('Données horaire invalides.');
}
if ($day !== null && ($day < 0 || $day > 6)) {
    http_response_code(422);
    die('Jour invalide.');
}

$stmt = $pdo->prepare('SELECT * FROM lotteries WHERE id=? LIMIT 1');
$stmt->execute([$lotteryId]);
$lottery = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($lottery ?: null, 'lottery');
$tenantId = (int)$lottery['tenant_id'];

$stmt = $pdo->prepare('INSERT INTO lottery_schedules(tenant_id, lottery_id, day_of_week, draw_time, close_before_minutes, status) VALUES (?, ?, ?, ?, ?, \'active\')');
$stmt->execute([$tenantId, $lotteryId, $day, $drawTime, $closeBefore]);

if (function_exists('saveLog')) {
    saveLog($pdo, current_user_id(), 'LOTTERY_SCHEDULE_CREATE', 'Horaire lottery ajouté');
}

header('Location: /views/lotteries/schedules.php' . (is_super_admin() ? '?tenant_id=' . $tenantId : ''));
exit;
