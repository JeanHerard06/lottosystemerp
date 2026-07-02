<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';

require_permission($pdo, 'controls.manage');
require_post();
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('DELETE FROM marriages WHERE id=?');
$stmt->execute([$id]);
audit_log($pdo, current_user_id(), 'DELETE_MARRIAGE', 'Mariage supprimé #' . $id);
redirect('../../views/marriages/index.php');
