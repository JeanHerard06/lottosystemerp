<?php
session_start();
require "../../config/database.php";
require_once "../../app/Helpers/security.php";
require_once "../../app/Helpers/csrf.php";
require_once "../../includes/auth.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'users.manage');
require_post();
verify_csrf();

$name = input_string('name', 100);
$username = input_string('username', 50);
$password = (string)($_POST['password'] ?? '');
if ($password === '') { die('Mot de passe obligatoire.'); }
if (strlen($password) < 8) { die('Le mot de passe doit contenir au moins 8 caractères.'); }

$role = normalize_system_role((string)($_POST['role'] ?? 'agent'));
$tenantId = is_super_admin()
    ? ((isset($_POST['tenant_id']) && $_POST['tenant_id'] !== '') ? (int)$_POST['tenant_id'] : current_tenant_id())
    : current_tenant_id();

if (!$tenantId) {
    die('Tenant obligatoire pour créer un utilisateur non super_admin.');
}

$roleIds = filter_allowed_role_ids($pdo, $_POST['role_ids'] ?? []);

$check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
$check->execute([$username]);
if ((int)$check->fetchColumn() > 0) { die('Identifiant déjà utilisé.'); }

$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO users(tenant_id, name, username, password, role, status) VALUES(?,?,?,?,?,1)");
$stmt->execute([$tenantId, $name, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
$userId = (int)$pdo->lastInsertId();

if (!$roleIds) {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug=? AND slug <> 'super_admin' LIMIT 1");
    $stmt->execute([$role]);
    $rid = $stmt->fetchColumn();
    if ($rid) { $roleIds = [(int)$rid]; }
}

$stmt = $pdo->prepare("INSERT INTO user_roles(user_id, role_id) VALUES(?,?)");
foreach ($roleIds as $rid) {
    $stmt->execute([$userId, $rid]);
}
$pdo->commit();
redirect('../../views/users/index.php');
