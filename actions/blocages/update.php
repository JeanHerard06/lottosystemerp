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
$number = trim((string)($_POST['number_value'] ?? '')) ?: '*';
$gameType = trim((string)($_POST['game_type'] ?? '')) ?: null;
$lotteryId = ($_POST['lottery_id'] ?? '') !== '' ? (int)$_POST['lottery_id'] : null;
$agencyId = ($_POST['agency_id'] ?? '') !== '' ? (int)$_POST['agency_id'] : null;
if ($agencyId) { ensure_agency_scope($pdo, $agencyId); }
$stmt = $pdo->prepare('SELECT * FROM blocages WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($row ?: null, 'blocage');
$motif = input_string('motif', 255, false);
$startsAt = trim((string)($_POST['starts_at'] ?? '')) ?: null;
$endsAt = trim((string)($_POST['ends_at'] ?? '')) ?: null;
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';
$stmt = $pdo->prepare('UPDATE blocages SET number_value=?, game_type=?, lottery_id=?, agency_id=?, motif=?, starts_at=?, ends_at=?, status=? WHERE id=? AND tenant_id=?');
$stmt->execute([$number, $gameType, $lotteryId, $agencyId, $motif, $startsAt, $endsAt, $status, $id, (int)$row['tenant_id']]);
audit_log($pdo, current_user_id(), 'UPDATE_BLOCAGE', 'Blocage modifié #' . $id);
redirect('../../views/blocages/index.php');
