<?php
require_once __DIR__ . '/auth.php';
$user = api_user($pdo); $agent = api_agent($pdo, $user);
$where = ''; $params = [];
if ($user['role'] === 'agent' && $agent) { $where = ' WHERE agent_id = ?'; $params[] = $agent['id']; }
$stmt = $pdo->prepare("SELECT COUNT(*) fiches, COALESCE(SUM(total_amount),0) ventes, COALESCE(SUM(gain_amount),0) gains FROM fiches $where");
$stmt->execute($params); $r = $stmt->fetch(PDO::FETCH_ASSOC);
api_response(true, ['data'=>['fiches'=>(int)$r['fiches'], 'ventes'=>(float)$r['ventes'], 'gains'=>(float)$r['gains'], 'profit'=>(float)$r['ventes']-(float)$r['gains']]]);
