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

$name = input_string('name', 100);
$username = input_string('username', 50);
$phone = input_string('phone', 30, false);
$agencyId = (int)($_POST['agency_id'] ?? 0);
$status = (int)($_POST['status'] ?? 1);
$status = $status === 1 ? 1 : 0;
if ($agencyId <= 0) { die('Agence obligatoire.'); }
$agency = ensure_agency_scope($pdo, $agencyId);

$commission = input_money('commission', false);
$borletteRate = input_money('borlette_rate', false);
$mariageRate = input_money('mariage_rate', false);
$lotto3Rate = input_money('lotto3_rate', false);
$lotto4Rate = input_money('lotto4_rate', false);
$passwordRaw = trim((string)($_POST['password'] ?? ''));

$stmt = $pdo->prepare('SELECT a.*, u.username FROM agents a JOIN users u ON u.id=a.user_id WHERE a.id=? LIMIT 1');
$stmt->execute([$id]);
$agent = $stmt->fetch();
ensure_record_tenant($agent, 'agent');

if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM supervisors WHERE user_id=? AND agency_id=? AND tenant_id=?');
    $stmt->execute([(int)$_SESSION['user_id'], (int)$agent['agency_id'], (int)$agent['tenant_id']]);
    if ((int)$stmt->fetchColumn() === 0) { http_response_code(403); die('Agent non autorisé.'); }

    $stmt->execute([(int)$_SESSION['user_id'], $agencyId, (int)$agency['tenant_id']]);
    if ((int)$stmt->fetchColumn() === 0) { http_response_code(403); die('Agence non autorisée.'); }
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username=? AND id<>?');
$stmt->execute([$username, (int)$agent['user_id']]);
if ((int)$stmt->fetchColumn() > 0) { die('Cet identifiant existe déjà.'); }

try {
    $pdo->beginTransaction();

    if ($passwordRaw !== '') {
        if (strlen($passwordRaw) < 6) { die('Mot de passe trop court.'); }
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET name=?, username=?, status=?, password=? WHERE id=? AND tenant_id=?');
        $stmt->execute([$name, $username, $status, $password, (int)$agent['user_id'], (int)$agent['tenant_id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name=?, username=?, status=? WHERE id=? AND tenant_id=?');
        $stmt->execute([$name, $username, $status, (int)$agent['user_id'], (int)$agent['tenant_id']]);
    }

    $stmt = $pdo->prepare('UPDATE agents SET agency_id=?, phone=?, commission=?, borlette_rate=?, mariage_rate=?, lotto3_rate=?, lotto4_rate=? WHERE id=? AND tenant_id=?');
    $stmt->execute([$agencyId, $phone, $commission, $borletteRate, $mariageRate, $lotto3Rate, $lotto4Rate, $id, (int)$agent['tenant_id']]);

    audit_log($pdo, (int)$_SESSION['user_id'], 'UPDATE_AGENT', 'Agent modifié: ' . $username);
    $pdo->commit();
    redirect('../views/agents.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die('Erreur modification agent: ' . e($e->getMessage()));
}
