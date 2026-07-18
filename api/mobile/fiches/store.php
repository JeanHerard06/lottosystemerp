<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/fiches.php';
require_once __DIR__ . '/../../../app/Helpers/risk.php';
require_once __DIR__ . '/../../../app/Helpers/finance.php';
require_once __DIR__ . '/../../../app/Helpers/cash_sessions.php';
require_once __DIR__ . '/../../../app/Helpers/lotteries.php';
require_once __DIR__ . '/../../../app/Helpers/game_engine.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$deviceId = trim($payload['device_id'] ?? '');
$localUuid = trim($payload['local_uuid'] ?? '');
$tenantId = $user['tenant_id'] ? (int)$user['tenant_id'] : null;
$lotteryId = !empty($payload['lottery_id']) ? (int)$payload['lottery_id'] : null;
$plays = $payload['plays'] ?? [];

if (!$plays || !is_array($plays)) {
    mobile_json(['success' => false, 'message' => 'Aucune ligne de jeu'], 422);
}

$lines = [];
$total = 0.0;
foreach ($plays as $i => $p) {
    $number = trim((string)($p['number'] ?? ''));
    $type = $p['type'] ?? 'borlette';
    $amount = (float)($p['amount'] ?? 0);
    $gameError = game_engine_validate_play($pdo, (string)$type, $number, $tenantId);
    if ($number === '' || $amount <= 0 || $gameError !== null) {
        mobile_json(['success' => false, 'message' => 'Ligne invalide #' . ($i + 1) . ($gameError ? ': ' . $gameError : '')], 422);
    }
    $lines[] = ['number'=>$number,'type'=>$type,'amount'=>$amount];
    $total += $amount;
}

try {
    $pdo->beginTransaction();
    validate_lottery_scope($pdo, $lotteryId, $tenantId);
    $cashSession = open_cash_session($pdo, (int)$agent['id']);
    if (!$cashSession) mobile_json(['success'=>false, 'message'=>'Aucune session de caisse ouverte'], 403);
    validate_risk_before_sale($pdo, $agent, $lotteryId, $lines);
    $ficheCode = unique_fiche_code($pdo, 'MOB');

    $syncedAt = TimeService::sqlNow();
    $stmt = $pdo->prepare("INSERT INTO fiches (tenant_id, agent_id, lottery_id, cash_session_id, fiche_code, total_amount, status, sync_source, device_id, local_uuid, sync_status, synced_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'mobile', ?, ?, 'synced', ?)");
    $stmt->execute([$tenantId, $agent['id'], $lotteryId, (int)$cashSession['id'], $ficheCode, $total, $deviceId, $localUuid ?: null, $syncedAt]);
    $ficheId = (int)$pdo->lastInsertId();

    $detail = $pdo->prepare("INSERT INTO fiche_details (fiche_id, number_played, play_type, amount) VALUES (?, ?, ?, ?)");
    foreach ($lines as $p) {
        $detail->execute([$ficheId, $p['number'], $p['type'], $p['amount']]);
    }

    post_agent_transaction($pdo, (int)$agent['id'], 'vente', $total, 'Vente mobile fiche ' . $ficheCode, (int)$user['id'], $ficheCode, (int)$cashSession['id']);

    // Mobile commission posting: same business rule as web sale.
    // Commission is grouped by game type to keep ledger clean and readable.
    $commissionTotals = [];
    foreach ($lines as $line) {
        $rateStmt = $pdo->prepare('SELECT percentage FROM commissions WHERE agent_id=? AND game_type=? LIMIT 1');
        $rateStmt->execute([(int)$agent['id'], $line['type']]);
        $pct = $rateStmt->fetchColumn();
        if ($pct === false) {
            $fallback = $line['type'] . '_rate';
            $pct = $agent[$fallback] ?? $agent['commission'] ?? 0;
        }
        $commissionTotals[$line['type']] = ($commissionTotals[$line['type']] ?? 0) + ((float)$line['amount'] * (float)$pct / 100);
    }

    foreach ($commissionTotals as $game => $commissionAmount) {
        if ($commissionAmount > 0) {
            post_agent_transaction(
                $pdo,
                (int)$agent['id'],
                'commission',
                (float)$commissionAmount,
                'Commission mobile ' . $game . ' fiche ' . $ficheCode,
                (int)$user['id'],
                $ficheCode,
                (int)$cashSession['id']
            );
        }
    }

    $pdo->commit();
    mobile_json(['success' => true, 'message' => 'Fiche enregistrée', 'fiche' => ['id' => $ficheId, 'fiche_code' => $ficheCode, 'total_amount' => $total]]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    mobile_json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
}
