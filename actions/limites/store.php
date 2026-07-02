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

$number = input_string('number_value', 10);
$gameType = trim((string)($_POST['game_type'] ?? '')) ?: null;
$lotteryId = ($_POST['lottery_id'] ?? '') !== '' ? (int)$_POST['lottery_id'] : null;
$agencyId = ($_POST['agency_id'] ?? '') !== '' ? (int)$_POST['agency_id'] : null;
if ($agencyId) { ensure_agency_scope($pdo, $agencyId); }
$tenantId = tenant_required_insert_id();
$maxAmount = input_money('max_amount');
$threshold = (float)($_POST['threshold_percent'] ?? 80);
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';

$stmt = $pdo->prepare('INSERT INTO limites (tenant_id, number_value, game_type, lottery_id, agency_id, max_amount, threshold_percent, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$tenantId, $number, $gameType, $lotteryId, $agencyId, $maxAmount, $threshold, $status]);
audit_log($pdo, current_user_id(), 'CREATE_LIMITE', 'Limite créée: ' . $number . ' / ' . $maxAmount);
redirect('../../views/limites/index.php');
