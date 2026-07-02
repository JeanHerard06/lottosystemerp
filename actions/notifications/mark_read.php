<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/notifications.php';

require_post();
verify_csrf();
require_permission($pdo, 'notifications.view');
$id = (int)($_POST['id'] ?? 0);
ensure_notification_scope($pdo, $id);
$stmt = $pdo->prepare('UPDATE notifications SET read_at = COALESCE(read_at, NOW()) WHERE id=?');
$stmt->execute([$id]);
header('Location: /views/notifications/index.php');
exit;
