<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/Helpers/security.php';
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/audit.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';


require_permission($pdo, 'tirages.manage');
require_post();
verify_csrf();

$tenantId = tenant_value();
$lotteryId = (int)($_POST['lottery_id'] ?? 0);
$drawName = input_string('draw_name', 100);
$first = input_string('first_number', 10);
$second = input_string('second_number', 10, false);
$third = input_string('third_number', 10, false);
$drawDate = input_string('draw_date', 10);

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $drawDate)) {
    die('Date tirage invalide.');
}

if ($lotteryId) {
    $sql = 'SELECT COUNT(*) FROM lotteries WHERE id=? AND status=1';
    $params = [$lotteryId];
    if ($tenantId && !is_super_admin()) {
        $sql .= ' AND (tenant_id=? OR tenant_id IS NULL)';
        $params[] = $tenantId;
    }
    $check = $pdo->prepare($sql);
    $check->execute($params);
    if ((int)$check->fetchColumn() === 0) {
        die('Lotterie inactive, introuvable ou hors tenant.');
    }
}

$stmt = $pdo->prepare("INSERT INTO tirages (tenant_id, lottery_id, draw_name, first_number, second_number, third_number, draw_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'open')");
$stmt->execute([$tenantId, $lotteryId ?: null, $drawName, $first, $second ?: null, $third ?: null, $drawDate]);
$tirageId = (int)$pdo->lastInsertId();
if ($lotteryId) {
    $stmt = $pdo->prepare("UPDATE lotteries SET sales_status='drawn', closed_at=COALESCE(closed_at, NOW()), closed_by=? WHERE id=?");
    $stmt->execute([current_user_id(), $lotteryId]);
}

audit_log($pdo, current_user_id(), 'CREATE_TIRAGE', 'Tirage créé: ' . $drawName . ' #' . $tirageId);

redirect('/views/tirages/show.php?id=' . $tirageId . '&created=1');
