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
$number = input_string('number_value', 10);
$gameType = trim((string)($_POST['game_type'] ?? '')) ?: null;
$lotteryId = ($_POST['lottery_id'] ?? '') !== '' ? (int)$_POST['lottery_id'] : null;
$agencyId = ($_POST['agency_id'] ?? '') !== '' ? (int)$_POST['agency_id'] : null;
if ($agencyId) { ensure_agency_scope($pdo, $agencyId); }
$stmt = $pdo->prepare('SELECT * FROM limites WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($row ?: null, 'limite');
$maxAmount = input_money('max_amount');
$threshold = (float)($_POST['threshold_percent'] ?? 80);
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';
$stmt = $pdo->prepare('UPDATE limites SET number_value=?, game_type=?, lottery_id=?, agency_id=?, max_amount=?, threshold_percent=?, status=? WHERE id=? AND tenant_id=?');
$stmt->execute([$number, $gameType, $lotteryId, $agencyId, $maxAmount, $threshold, $status, $id, (int)$row['tenant_id']]);
audit_log($pdo, current_user_id(), 'UPDATE_LIMITE', 'Limite modifiée #' . $id);
redirect('../../views/limites/index.php');
