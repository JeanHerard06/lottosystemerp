<?php
require_once __DIR__ . '/auth.php';
$user = api_user($pdo); $agent = api_agent($pdo, $user);
$sql = "SELECT f.id, f.fiche_code, f.total_amount, f.gain_amount, f.status, f.created_at, u.name agent_name FROM fiches f JOIN agents a ON a.id=f.agent_id JOIN users u ON u.id=a.user_id";
$params=[]; if($user['role']==='agent' && $agent){ $sql.=' WHERE f.agent_id=?'; $params[]=$agent['id']; }
$sql .= ' ORDER BY f.id DESC LIMIT 100';
$stmt=$pdo->prepare($sql); $stmt->execute($params);
api_response(true, ['data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
