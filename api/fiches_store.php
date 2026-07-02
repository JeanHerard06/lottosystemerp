<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/Helpers/fiches.php';
require_once __DIR__ . '/../app/Helpers/risk.php';
require_once __DIR__ . '/../app/Helpers/finance.php';
require_once __DIR__ . '/../app/Helpers/cash_sessions.php';
require_once __DIR__ . '/../app/Helpers/lotteries.php';

$user = api_user($pdo);
$agent = api_agent($pdo, $user);
if (!$agent) api_response(false, ['message'=>'Compte agent introuvable'], 403);

$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) $payload = $_POST;
$items = $payload['items'] ?? [];
if (!is_array($items) || count($items) < 1) api_response(false, ['message'=>'Aucune ligne de fiche'], 422);

$tenantId = $user['tenant_id'] ? (int)$user['tenant_id'] : null;
$lotteryId = !empty($payload['lottery_id']) ? (int)$payload['lottery_id'] : null;
$allowedTypes = ['borlette','mariage','lotto3','lotto4'];
$lines = [];
$total = 0.0;
foreach ($items as $i => $it) {
    $number = trim((string)($it['number'] ?? ''));
    $type = (string)($it['type'] ?? 'borlette');
    $amount = (float)($it['amount'] ?? 0);
    if ($number === '' || !preg_match('/^[0-9]{1,4}(\-[0-9]{1,4})?$/', $number)) api_response(false,['message'=>'Numéro invalide ligne '.($i+1)],422);
    if (!in_array($type, $allowedTypes, true)) api_response(false,['message'=>'Type de jeu invalide'],422);
    if ($amount <= 0) api_response(false,['message'=>'Montant invalide'],422);
    $lines[] = ['number'=>$number,'type'=>$type,'amount'=>$amount];
    $total += $amount;
}

try {
    $pdo->beginTransaction();
    validate_lottery_scope($pdo, $lotteryId, $tenantId);
    $cashSession = open_cash_session($pdo, (int)$agent['id']);
    if (!$cashSession) api_response(false, ['message'=>'Aucune session de caisse ouverte'], 403);
    validate_risk_before_sale($pdo, $agent, $lotteryId, $lines);
    $code = unique_fiche_code($pdo, 'PWA');
    $stmt=$pdo->prepare('INSERT INTO fiches(tenant_id, agent_id, lottery_id, cash_session_id, fiche_code, total_amount, status, sync_source) VALUES(?,?,?,?,?,?,\'pending\',\'mobile\')');
    $stmt->execute([$tenantId, $agent['id'], $lotteryId, (int)$cashSession['id'], $code, $total]);
    $fiche_id = (int)$pdo->lastInsertId();
    $line=$pdo->prepare('INSERT INTO fiche_details(fiche_id, number_played, play_type, amount) VALUES(?,?,?,?)');
    foreach($lines as $it){ $line->execute([$fiche_id, $it['number'], $it['type'], $it['amount']]); }
    post_agent_transaction($pdo, (int)$agent['id'], 'vente', $total, 'Vente PWA '.$code, (int)$user['id'], $code, (int)$cashSession['id']);
    $pdo->commit();
    api_response(true, ['message'=>'Fiche enregistrée','data'=>['id'=>$fiche_id,'fiche_code'=>$code,'total_amount'=>$total]]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    api_response(false, ['message'=>'Erreur: '.$e->getMessage()], 500);
}
