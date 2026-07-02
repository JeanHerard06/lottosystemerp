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
$name = input_string('name', 100);
$status = ($_POST['status'] ?? '1') === '1' ? 1 : 0;
$drawTime = trim((string)($_POST['draw_time'] ?? ''));
$drawTime = $drawTime !== '' ? $drawTime : null;
$closeBeforeMinutes = max(0, (int)($_POST['close_before_minutes'] ?? 10));
$salesStatus = $_POST['sales_status'] ?? 'open';
if (!in_array($salesStatus, ['open','closed','drawn'], true)) { $salesStatus = 'open'; }
$autoCloseEnabled = isset($_POST['auto_close_enabled']) ? 1 : 0;

$stmt = $pdo->prepare('SELECT * FROM lotteries WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$lottery = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($lottery, 'lottery');

$tenantId = is_super_admin() ? (int)($_POST['tenant_id'] ?? $lottery['tenant_id']) : (int)$lottery['tenant_id'];
if (!$tenantId) {
    die('Tenant obligatoire.');
}

try {
    $stmt = $pdo->prepare('UPDATE lotteries SET tenant_id=?, name=?, status=?, draw_time=?, close_before_minutes=?, sales_status=?, auto_close_enabled=? WHERE id=?');
    $stmt->execute([$tenantId, $name, $status, $drawTime, $closeBeforeMinutes, $salesStatus, $autoCloseEnabled, $id]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'UPDATE_LOTTERY', 'Lottery modifiée: ' . $name);
    redirect('../../views/lotteries/index.php');
} catch (Throwable $e) {
    die('Erreur modification lottery: ' . e($e->getMessage()));
}
