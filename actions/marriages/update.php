<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_permission($pdo, 'controls.manage');
require_post();
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$n1 = input_string('number1', 5);
$n2 = input_string('number2', 5);
$gameType = trim((string)($_POST['game_type'] ?? 'mariage')) ?: 'mariage';
$lotteryId = ($_POST['lottery_id'] ?? '') !== '' ? (int)$_POST['lottery_id'] : null;
$stmt = $pdo->prepare('SELECT * FROM marriages WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($row ?: null, 'mariage');
$payout = input_money('payout');
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';
$stmt = $pdo->prepare('UPDATE marriages SET number1=?, number2=?, game_type=?, lottery_id=?, payout=?, status=? WHERE id=? AND tenant_id=?');
$stmt->execute([$n1, $n2, $gameType, $lotteryId, $payout, $status, $id, (int)$row['tenant_id']]);
audit_log($pdo, current_user_id(), 'UPDATE_MARRIAGE', 'Mariage modifié #' . $id);
redirect('../../views/marriages/index.php');
