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
$rate = input_money('payout_rate');
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';
$stmt = $pdo->prepare('UPDATE primes SET payout_rate=?, status=? WHERE id=?');
$stmt->execute([$rate, $status, $id]);
audit_log($pdo, current_user_id(), 'UPDATE_PRIME', 'Prime modifiée #' . $id . ' => ' . $rate);
redirect('../../views/primes/index.php');
