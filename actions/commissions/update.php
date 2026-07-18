<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/game_engine.php';

require_permission($pdo, 'commissions.manage');
require_post();
verify_csrf();

$agentId = (int)($_POST['agent_id'] ?? 0);
$rates = $_POST['rates'] ?? [];
$allowed = array_column(game_engine_types($pdo, current_tenant_id() ?? 0, true), 'code');
if ($agentId <= 0) die('Agent invalide.');

$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO commissions(agent_id, game_type, percentage) VALUES(?,?,?) ON DUPLICATE KEY UPDATE percentage=VALUES(percentage)');
foreach ($allowed as $game) {
    $pct = max(0, min(100, (float)($rates[$game] ?? 0)));
    $stmt->execute([$agentId, $game, $pct]);
}
audit_log($pdo, current_user_id(), 'UPDATE_COMMISSIONS', 'Commissions agent #' . $agentId . ' mises à jour');
$pdo->commit();
header('Location: /views/commissions/index.php?agent_id=' . $agentId);
exit;
