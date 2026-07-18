<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/game_engine.php';
require_permission($pdo, 'controls.manage');
require_post();
verify_csrf();

$tenantId = current_tenant_id() ?? 0;
$original = game_engine_normalize_code((string)($_POST['original_code'] ?? ''));
$code = game_engine_normalize_code((string)($_POST['code'] ?? ''));
$name = trim((string)($_POST['name'] ?? ''));
$pattern = trim((string)($_POST['validation_pattern'] ?? ''));
$hint = trim((string)($_POST['input_hint'] ?? ''));
$engine = trim((string)($_POST['matching_engine'] ?? 'exact_first'));
$order = (int)($_POST['display_order'] ?? 100);
$enabled = isset($_POST['enabled']) ? 1 : 0;
$lotteryId = max(0, (int)($_POST['lottery_id'] ?? 0));
$allowedEngines = ['borlette_position','marriage_any','exact_sequence3','exact_first','any_draw'];
if ($code === '' || $name === '' || !in_array($engine, $allowedEngines, true)) {
    http_response_code(422); die('Données du jeu invalides.');
}
if ($pattern !== '' && @preg_match('~' . str_replace('~', '\\~', $pattern) . '~u', '') === false) {
    http_response_code(422); die('Expression régulière invalide.');
}

// Never overwrite the global template from a tenant screen: create/update tenant override.
$sql = "INSERT INTO game_types (tenant_id, code, name, enabled, display_order, validation_pattern, input_hint, matching_engine)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name=VALUES(name), enabled=VALUES(enabled), display_order=VALUES(display_order),
        validation_pattern=VALUES(validation_pattern), input_hint=VALUES(input_hint), matching_engine=VALUES(matching_engine)";
if ($lotteryId > 0) {
    $check = $pdo->prepare('SELECT COUNT(*) FROM lotteries WHERE id=?' . (is_super_admin() ? '' : ' AND tenant_id=?'));
    $check->execute(is_super_admin() ? [$lotteryId] : [$lotteryId, $tenantId]);
    if ((int)$check->fetchColumn() === 0) { http_response_code(403); die('Lottery hors tenant.'); }
}
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tenantId, $code, $name, $enabled, $order, $pattern ?: null, $hint ?: null, $engine]);
    if ($original !== '' && $original !== $code) {
        $pdo->prepare('DELETE FROM game_types WHERE tenant_id=? AND code=?')->execute([$tenantId, $original]);
        $pdo->prepare('UPDATE game_payout_rules SET game_code=? WHERE tenant_id=? AND game_code=?')->execute([$code,$tenantId,$original]);
    }
    $rules = [];
    if ($engine === 'borlette_position') {
        foreach ([1,2,3] as $pos) {
            $raw = str_replace(',', '.', trim((string)($_POST['payout_position_'.$pos] ?? '')));
            if ($raw !== '' && is_numeric($raw) && (float)$raw >= 0) $rules['position_'.$pos] = (float)$raw;
        }
    } else {
        $raw = str_replace(',', '.', trim((string)($_POST['payout_exact'] ?? '')));
        if ($raw !== '' && is_numeric($raw) && (float)$raw >= 0) $rules['exact'] = (float)$raw;
    }
    $ruleStmt = $pdo->prepare("INSERT INTO game_payout_rules(tenant_id,lottery_id,game_code,match_level,multiplier,enabled) VALUES(?,?,?,?,?,1) ON DUPLICATE KEY UPDATE multiplier=VALUES(multiplier), enabled=1");
    foreach ($rules as $level=>$multiplier) $ruleStmt->execute([$tenantId,$lotteryId,$code,$level,$multiplier]);
    audit_log($pdo, current_user_id(), 'SAVE_GAME_TYPE', "Jeu {$code} tenant={$tenantId} lottery={$lotteryId}");
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500); die('Erreur Game Engine: '.e($e->getMessage()));
}
redirect('../../views/games/index.php?lottery_id=' . $lotteryId . '&saved=1');
