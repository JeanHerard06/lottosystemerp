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
[$where, $params] = notification_scope_clause($pdo, 'n', 'WHERE');
$sql = "UPDATE notifications n SET n.read_at = COALESCE(n.read_at, NOW()) $where AND n.read_at IS NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
header('Location: /views/notifications/index.php');
exit;
