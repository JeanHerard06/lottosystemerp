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

$n1 = input_string('number1', 5);
$n2 = input_string('number2', 5);
$gameType = trim((string)($_POST['game_type'] ?? 'mariage')) ?: 'mariage';
$lotteryId = ($_POST['lottery_id'] ?? '') !== '' ? (int)$_POST['lottery_id'] : null;
$tenantId = tenant_required_insert_id();
$payout = input_money('payout');
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';
$stmt = $pdo->prepare('INSERT INTO marriages (tenant_id, number1, number2, game_type, lottery_id, payout, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$tenantId, $n1, $n2, $gameType, $lotteryId, $payout, $status]);
audit_log($pdo, current_user_id(), 'CREATE_MARRIAGE', 'Mariage créé: ' . $n1 . '-' . $n2);
redirect('../../views/marriages/index.php');
