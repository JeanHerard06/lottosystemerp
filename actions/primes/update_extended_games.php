<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';

require_permission($pdo, 'controls.manage');
require_post();
verify_csrf();

$lotteryId = max(0, (int)($_POST['lottery_id'] ?? 0));
$tenantId = current_tenant_id() ?? 0;

if ($lotteryId > 0) {
    $stmt = $pdo->prepare('SELECT id, tenant_id, name FROM lotteries WHERE id = ? LIMIT 1');
    $stmt->execute([$lotteryId]);
    $lottery = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lottery) {
        http_response_code(404);
        die('Lottery introuvable.');
    }
    if (!is_super_admin() && (int)($lottery['tenant_id'] ?? 0) !== (int)$tenantId) {
        http_response_code(403);
        die('Accès refusé: lottery hors tenant.');
    }
}

$inputMap = [
    'payout_mariage' => 'Mariage',
    'payout_lotto3' => 'Lotto 3',
    'payout_lotto4' => 'Lotto 4',
];
$values = [];
foreach ($inputMap as $key => $label) {
    $raw = str_replace(',', '.', trim((string)($_POST[$key] ?? '')));
    if ($raw === '' || !is_numeric($raw) || (float)$raw < 0) {
        http_response_code(422);
        die('Multiplicateur invalide pour ' . $label . '.');
    }
    $values[$key] = round((float)$raw, 4);
}

$sql = "INSERT INTO game_settings (tenant_id, lottery_id, setting_key, setting_value)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
$stmt = $pdo->prepare($sql);

$pdo->beginTransaction();
try {
    foreach ($values as $key => $value) {
        $stmt->execute([$tenantId, $lotteryId, $key, $value]);
    }
    audit_log(
        $pdo,
        current_user_id(),
        'UPDATE_EXTENDED_GAME_PAYOUTS',
        sprintf(
            'Barème tenant=%d lottery=%d: mariage=%.2f, lotto3=%.2f, lotto4=%.2f',
            $tenantId,
            $lotteryId,
            $values['payout_mariage'],
            $values['payout_lotto3'],
            $values['payout_lotto4']
        )
    );
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    die('Erreur lors de la mise à jour des règles: ' . e($e->getMessage()));
}

redirect('../../views/primes/index.php?lottery_id=' . $lotteryId . '&saved_games=1');
