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

$name = input_string('name', 100);
$username = input_string('username', 50);
$phone = input_string('phone', 30, false);
$agencyId = (int)($_POST['agency_id'] ?? 0);
if ($agencyId <= 0) { die('Agence obligatoire.'); }
$agency = ensure_agency_scope($pdo, $agencyId);
$tenantId = (int)$agency['tenant_id'];
if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM supervisors WHERE user_id=? AND agency_id=? AND tenant_id=?');
    $stmt->execute([(int)$_SESSION['user_id'], $agencyId, $tenantId]);
    if ((int)$stmt->fetchColumn() === 0) { http_response_code(403); die('Agence non autorisée.'); }
}
$commission = input_money('commission', false);
$passwordRaw = (string)($_POST['password'] ?? '');
if (strlen($passwordRaw) < 6) { die('Mot de passe trop court.'); }
$password = password_hash($passwordRaw, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username=?');
$stmt->execute([$username]);
if ((int)$stmt->fetchColumn() > 0) { die('Cet identifiant existe déjà.'); }

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO users (tenant_id, name, username, password, role, status) VALUES (?, ?, ?, ?, 'agent', 1)");
    $stmt->execute([$tenantId, $name, $username, $password]);
    $user_id = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare('INSERT INTO agents (tenant_id, user_id, agency_id, phone, commission, balance) VALUES (?, ?, ?, ?, ?, 0)');
    $stmt->execute([$tenantId, $user_id, $agencyId, $phone, $commission]);
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) SELECT ?, id FROM roles WHERE slug='agent'");
    $stmt->execute([$user_id]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'CREATE_AGENT', 'Agent créé: ' . $username);
    $pdo->commit();
    redirect('../views/agents.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die('Erreur création agent: ' . e($e->getMessage()));
}
