<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/Helpers/security.php';
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/audit.php';
require_once __DIR__ . '/../app/Helpers/fiches.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_once __DIR__ . '/../app/Helpers/risk.php';
require_once __DIR__ . '/../app/Helpers/finance.php';
require_once __DIR__ . '/../app/Helpers/cash_sessions.php';
require_once __DIR__ . '/../app/Helpers/lotteries.php';

require_permission($pdo, 'fiches.create');
require_post();
verify_csrf();

$agent = current_agent($pdo);
if (!$agent) {
    die('Compte agent introuvable. Seul un compte agent peut vendre une fiche.');
}

$tenantId = tenant_value();
$lotteryId = isset($_POST['lottery_id']) && $_POST['lottery_id'] !== '' ? (int)$_POST['lottery_id'] : null;
$numbers = $_POST['numbers'] ?? [];
$types = $_POST['types'] ?? [];
$amounts = $_POST['amounts'] ?? [];
$allowedTypes = ['borlette', 'mariage', 'lotto3', 'lotto4'];

if (!$numbers || count($numbers) !== count($types) || count($numbers) !== count($amounts)) {
    die('Fiche invalide: lignes incomplètes.');
}

$lines = [];
$total = 0.0;
foreach ($numbers as $i => $rawNumber) {
    $number = trim((string)$rawNumber);
    $type = trim((string)$types[$i]);
    $amount = (float)$amounts[$i];

    if ($number === '' || !preg_match('/^[0-9]{1,4}(\-[0-9]{1,4})?$/', $number)) {
        die('Numéro invalide sur la ligne ' . ($i + 1));
    }
    if (!in_array($type, $allowedTypes, true)) {
        die('Type de jeu invalide sur la ligne ' . ($i + 1));
    }
    if ($amount <= 0) {
        die('Montant invalide sur la ligne ' . ($i + 1));
    }

    $lines[] = ['number' => $number, 'type' => $type, 'amount' => $amount];
    $total += $amount;
}

try {
    $pdo->beginTransaction();

    validate_lottery_scope($pdo, $lotteryId, $tenantId);
    $cashSession = require_open_cash_session($pdo, (int)$agent['id']);
    validate_risk_before_sale($pdo, $agent, $lotteryId, $lines);

    $code = unique_fiche_code($pdo, 'FCH');

    $stmt = $pdo->prepare('INSERT INTO fiches (tenant_id, agent_id, lottery_id, cash_session_id, fiche_code, total_amount, status, sync_source) VALUES (?, ?, ?, ?, ?, ?, "pending", "web")');
    $stmt->execute([$tenantId, (int)$agent['id'], $lotteryId, (int)$cashSession['id'], $code, $total]);
    $ficheId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO fiche_details (fiche_id, number_played, play_type, amount) VALUES (?, ?, ?, ?)');
    foreach ($lines as $line) {
        $stmt->execute([$ficheId, $line['number'], $line['type'], $line['amount']]);
    }

    post_agent_transaction($pdo, (int)$agent['id'], 'vente', $total, 'Vente fiche ' . $code, current_user_id(), null, (int)$cashSession['id']);

    $commissionTotals = [];
    foreach ($lines as $line) {
        $stmt = $pdo->prepare('SELECT percentage FROM commissions WHERE agent_id=? AND game_type=? LIMIT 1');
        $stmt->execute([(int)$agent['id'], $line['type']]);
        $pct = $stmt->fetchColumn();
        if ($pct === false) {
            $fallback = $line['type'] . '_rate';
            $pct = $agent[$fallback] ?? $agent['commission'] ?? 0;
        }
        $commissionTotals[$line['type']] = ($commissionTotals[$line['type']] ?? 0) + ((float)$line['amount'] * (float)$pct / 100);
    }
    foreach ($commissionTotals as $game => $commissionAmount) {
        if ($commissionAmount > 0) {
            post_agent_transaction($pdo, (int)$agent['id'], 'commission', $commissionAmount, 'Commission ' . $game . ' fiche ' . $code, current_user_id(), null, (int)$cashSession['id']);
        }
    }

    audit_log($pdo, current_user_id(), 'CREATE_FICHE', 'Fiche créée: ' . $code . ' | Total: ' . $total);
    $pdo->commit();

    redirect('../views/fiches/show.php?id=' . $ficheId . '&created=1');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Erreur: ' . e($e->getMessage()));
}
