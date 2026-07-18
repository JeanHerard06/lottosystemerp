<?php
require_once __DIR__ . '/../auth.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$tenantId = (int)($user['tenant_id'] ?? 0);
$agentId = (int)$agent['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$code = trim((string)($_GET['code'] ?? $_GET['fiche_code'] ?? ''));
$localUuid = trim((string)($_GET['local_uuid'] ?? ''));

if ($id <= 0 && $code === '' && $localUuid === '') {
    mobile_json(['success' => false, 'message' => 'Fiche introuvable.'], 422);
}

$where = ['f.agent_id = ?'];
$params = [$agentId];
if (mobile_api_has_column($pdo, 'fiches', 'tenant_id') && $tenantId > 0) { $where[] = 'f.tenant_id = ?'; $params[] = $tenantId; }
$identity = [];
if ($id > 0) { $identity[] = 'f.id = ?'; $params[] = $id; }
if ($code !== '') { $identity[] = 'f.fiche_code = ?'; $params[] = $code; }
if ($localUuid !== '' && mobile_api_has_column($pdo, 'fiches', 'local_uuid')) { $identity[] = 'f.local_uuid = ?'; $params[] = $localUuid; }
$where[] = '(' . implode(' OR ', $identity) . ')';

$selectTirage = mobile_api_has_column($pdo, 'fiches', 'tirage_id');
$tirageJoin = $selectTirage ? 'LEFT JOIN tirages t ON t.id = f.tirage_id' : '';
$tirageCols = $selectTirage ? ', t.draw_name AS tirage_name, t.draw_date AS tirage_date' : ', NULL AS tirage_name, NULL AS tirage_date';

$stmt = $pdo->prepare("SELECT f.*, l.name AS lottery_name {$tirageCols}
    FROM fiches f
    LEFT JOIN lotteries l ON l.id = f.lottery_id
    {$tirageJoin}
    WHERE " . implode(' AND ', $where) . "
    ORDER BY f.id DESC
    LIMIT 1");
$stmt->execute($params);
$fiche = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fiche) {
    mobile_json(['success' => false, 'message' => 'Fiche introuvable.'], 404);
}

$detailsStmt = $pdo->prepare("SELECT id, number_played, play_type, amount, created_at FROM fiche_details WHERE fiche_id = ? ORDER BY id ASC");
$detailsStmt->execute([(int)$fiche['id']]);
$details = $detailsStmt->fetchAll(PDO::FETCH_ASSOC);

mobile_json(['success' => true, 'fiche' => $fiche, 'details' => $details, 'data' => ['fiche' => $fiche, 'details' => $details]]);
