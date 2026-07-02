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

$name = input_string('name', 100);
$status = ($_POST['status'] ?? '1') === '1' ? 1 : 0;
$drawTime = trim((string)($_POST['draw_time'] ?? ''));
$drawTime = $drawTime !== '' ? $drawTime : null;
$closeBeforeMinutes = max(0, (int)($_POST['close_before_minutes'] ?? 10));
$salesStatus = $_POST['sales_status'] ?? 'open';
if (!in_array($salesStatus, ['open','closed','drawn'], true)) { $salesStatus = 'open'; }
$autoCloseEnabled = isset($_POST['auto_close_enabled']) ? 1 : 0;
$tenantId = tenant_insert_id();

if (!$tenantId && !is_super_admin()) {
    http_response_code(403);
    die('Aucun tenant actif.');
}
if (is_super_admin() && !$tenantId) {
    die('Tenant obligatoire pour créer une lottery.');
}

try {
    $stmt = $pdo->prepare('INSERT INTO lotteries (tenant_id, name, status, draw_time, close_before_minutes, sales_status, auto_close_enabled) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$tenantId, $name, $status, $drawTime, $closeBeforeMinutes, $salesStatus, $autoCloseEnabled]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'CREATE_LOTTERY', 'Lottery créée: ' . $name);
    redirect('../../views/lotteries/index.php');
} catch (Throwable $e) {
    die('Erreur création lottery: ' . e($e->getMessage()));
}
