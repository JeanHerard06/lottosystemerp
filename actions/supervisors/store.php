<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_post();
verify_csrf();
require_permission($pdo, 'supervisors.manage');

$name = input_string('name', 100);
$username = input_string('username', 50);
$agencyId = (int)($_POST['agency_id'] ?? 0);
$agency = ensure_agency_scope($pdo, $agencyId);
$tenantId = (int)$agency['tenant_id'];
$passwordRaw = (string)($_POST['password'] ?? '');
if (strlen($passwordRaw) < 6) { die('Mot de passe trop court.'); }
$password = password_hash($passwordRaw, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username=?');
$stmt->execute([$username]);
if ((int)$stmt->fetchColumn() > 0) { die('Cet identifiant existe déjà.'); }

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO users (tenant_id, name, username, password, role, status) VALUES (?, ?, ?, ?, 'superviseur', 1)");
    $stmt->execute([$tenantId, $name, $username, $password]);
    $userId = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare('INSERT INTO supervisors (tenant_id, user_id, agency_id) VALUES (?, ?, ?)');
    $stmt->execute([$tenantId, $userId, $agencyId]);
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) SELECT ?, id FROM roles WHERE slug='superviseur'");
    $stmt->execute([$userId]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'CREATE_SUPERVISOR', 'Superviseur créé: ' . $username . ' tenant #' . $tenantId);
    $pdo->commit();
    redirect('../../views/supervisors/index.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die('Erreur création superviseur: ' . e($e->getMessage()));
}
