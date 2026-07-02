<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../app/Helpers/security.php';
require_once __DIR__ . '/../app/Helpers/audit.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';

require_post();
verify_csrf();
require_permission($pdo, 'agents.manage');

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { die('Agent invalide.'); }

$stmt = $pdo->prepare('SELECT a.*, u.status, u.username FROM agents a JOIN users u ON u.id=a.user_id WHERE a.id=? LIMIT 1');
$stmt->execute([$id]);
$agent = $stmt->fetch();
ensure_record_tenant($agent, 'agent');

if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM supervisors WHERE user_id=? AND agency_id=?');
    $stmt->execute([(int)$_SESSION['user_id'], (int)$agent['agency_id']]);
    if ((int)$stmt->fetchColumn() === 0) { http_response_code(403); die('Agent non autorisé.'); }
}

$newStatus = ((int)$agent['status'] === 1) ? 0 : 1;
$stmt = $pdo->prepare('UPDATE users SET status=? WHERE id=?');
$stmt->execute([$newStatus, (int)$agent['user_id']]);
audit_log($pdo, (int)$_SESSION['user_id'], 'TOGGLE_AGENT', 'Statut agent changé: ' . $agent['username']);
redirect('../views/agents.php');
