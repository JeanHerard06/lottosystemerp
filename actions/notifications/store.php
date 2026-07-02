<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/notifications.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';

require_post();
verify_csrf();
require_permission($pdo, 'notifications.manage');

$title = input_string('title', 180);
$message = trim((string)($_POST['message'] ?? ''));
$type = $_POST['type'] ?? 'info';
$link = trim((string)($_POST['link_url'] ?? ''));
$link = $link !== '' ? $link : null;
$userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$tenantId = is_super_admin() ? (!empty($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : null) : current_tenant_id();

if ($message === '') { die('Message requis.'); }

if ($userId) {
    $stmt = $pdo->prepare('SELECT id, tenant_id FROM users WHERE id=? LIMIT 1');
    $stmt->execute([$userId]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target) { die('Utilisateur cible introuvable.'); }
    if (!is_super_admin() && (int)$target['tenant_id'] !== (int)current_tenant_id()) {
        http_response_code(403); die('Utilisateur cible hors tenant.');
    }
    if (!$tenantId && !empty($target['tenant_id'])) { $tenantId = (int)$target['tenant_id']; }
}

create_notification($pdo, $tenantId, $userId, $title, $message, $type, $link, current_user_id());
audit_log($pdo, current_user_id(), 'CREATE_NOTIFICATION', 'Notification créée: ' . $title);
header('Location: /views/notifications/index.php');
exit;
