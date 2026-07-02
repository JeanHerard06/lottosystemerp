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
$id = (int)($_POST['id'] ?? 0);
$notification = ensure_notification_scope($pdo, $id);
$stmt = $pdo->prepare('DELETE FROM notifications WHERE id=?');
$stmt->execute([$id]);
audit_log($pdo, current_user_id(), 'DELETE_NOTIFICATION', 'Notification supprimée: ' . ($notification['title'] ?? $id));
header('Location: /views/notifications/index.php');
exit;
