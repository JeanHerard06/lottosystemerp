<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';

require_post();
verify_csrf();
require_permission($pdo, 'logs.manage');

$days = max(30, (int)($_POST['days'] ?? 180));

if (is_super_admin()) {
    $stmt = $pdo->prepare('DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
    $stmt->execute([$days]);
} else {
    $stmt = $pdo->prepare('DELETE FROM audit_logs WHERE tenant_id=? AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
    $stmt->execute([current_tenant_id(), $days]);
}

audit_log($pdo, current_user_id(), 'PURGE_AUDIT_LOGS', 'Purge logs plus anciens que ' . $days . ' jours');
header('Location: /views/logs/index.php');
exit;
