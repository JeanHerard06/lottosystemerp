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

$stmt = $pdo->prepare('SELECT a.*, u.username FROM agents a JOIN users u ON u.id=a.user_id WHERE a.id=? LIMIT 1');
$stmt->execute([$id]);
$agent = $stmt->fetch();
ensure_record_tenant($agent, 'agent');

if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM supervisors WHERE user_id=? AND agency_id=?');
    $stmt->execute([(int)$_SESSION['user_id'], (int)$agent['agency_id']]);
    if ((int)$stmt->fetchColumn() === 0) { http_response_code(403); die('Agent non autorisé.'); }
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM fiches WHERE agent_id=?');
$stmt->execute([$id]);
$hasFiches = (int)$stmt->fetchColumn() > 0;

if ($hasFiches) {
    $stmt = $pdo->prepare('UPDATE users SET status=0 WHERE id=?');
    $stmt->execute([(int)$agent['user_id']]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'DISABLE_AGENT', 'Agent désactivé au lieu de suppression: ' . $agent['username']);
} else {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
        $stmt->execute([(int)$agent['user_id']]);
        audit_log($pdo, (int)$_SESSION['user_id'], 'DELETE_AGENT', 'Agent supprimé: ' . $agent['username']);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        die('Erreur suppression agent: ' . e($e->getMessage()));
    }
}

redirect('../views/agents.php');
