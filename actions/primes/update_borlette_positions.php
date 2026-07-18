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

$values = [];
foreach ([1, 2, 3] as $position) {
    $raw = str_replace(',', '.', trim((string)($_POST['payout_' . $position] ?? '')));
    if ($raw === '' || !is_numeric($raw) || (float)$raw < 0) {
        http_response_code(422);
        die('Multiplicateur invalide pour le lot #' . $position . '.');
    }
    $values[$position] = round((float)$raw, 4);
}

$tenantId = current_tenant_id() ?? 0;
$sql = "INSERT INTO game_settings (tenant_id, lottery_id, setting_key, setting_value)
        VALUES (?, 0, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
$stmt = $pdo->prepare($sql);

$pdo->beginTransaction();
try {
    foreach ($values as $position => $value) {
        $stmt->execute([$tenantId, 'payout_' . $position, $value]);
    }
    audit_log(
        $pdo,
        current_user_id(),
        'UPDATE_BORLETTE_PAYOUTS',
        sprintf('Barème Bòlèt tenant=%d: %.2f / %.2f / %.2f', $tenantId, $values[1], $values[2], $values[3])
    );
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    die('Erreur lors de la mise à jour du barème: ' . e($e->getMessage()));
}

redirect('../../views/primes/index.php?saved=1');
