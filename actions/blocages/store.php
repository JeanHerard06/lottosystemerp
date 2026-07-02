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

$number = trim((string)($_POST['number_value'] ?? '')) ?: '*';
$gameType = trim((string)($_POST['game_type'] ?? '')) ?: null;
$lotteryId = ($_POST['lottery_id'] ?? '') !== '' ? (int)$_POST['lottery_id'] : null;
$agencyId = ($_POST['agency_id'] ?? '') !== '' ? (int)$_POST['agency_id'] : null;
if ($agencyId) { ensure_agency_scope($pdo, $agencyId); }
$tenantId = tenant_required_insert_id();
$motif = input_string('motif', 255, false);
$startsAt = trim((string)($_POST['starts_at'] ?? '')) ?: null;
$endsAt = trim((string)($_POST['ends_at'] ?? '')) ?: null;
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';
$stmt = $pdo->prepare('INSERT INTO blocages (tenant_id, number_value, game_type, lottery_id, agency_id, motif, starts_at, ends_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$tenantId, $number, $gameType, $lotteryId, $agencyId, $motif, $startsAt, $endsAt, $status]);
audit_log($pdo, current_user_id(), 'CREATE_BLOCAGE', 'Blocage créé: ' . $number);
redirect('../../views/blocages/index.php');
