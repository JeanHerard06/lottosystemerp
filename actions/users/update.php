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

$id = (int)($_POST['id'] ?? 0);
$existing = assert_user_mutable($pdo, $id);

$name = input_string('name', 100);
$username = input_string('username', 50);
$status = (int)($_POST['status'] ?? 1) === 1 ? 1 : 0;

// On ne laisse jamais un tenant donner ou conserver le rôle super_admin.
if ((string)$existing['role'] === 'super_admin') {
    require_super_admin();
    $role = 'super_admin';
    $tenantId = null;
    $roleIds = [];
} else {
    $role = normalize_system_role((string)($_POST['role'] ?? 'agent'));
    $tenantId = is_super_admin()
        ? ((isset($_POST['tenant_id']) && $_POST['tenant_id'] !== '') ? (int)$_POST['tenant_id'] : (int)$existing['tenant_id'])
        : current_tenant_id();
    if (!$tenantId) { die('Tenant obligatoire.'); }
    $roleIds = filter_allowed_role_ids($pdo, $_POST['role_ids'] ?? []);
}

$check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?');
$check->execute([$username, $id]);
if ((int)$check->fetchColumn() > 0) { die('Identifiant déjà utilisé.'); }

$pdo->beginTransaction();
if (!empty($_POST['password'])) {
    if (strlen((string)$_POST['password']) < 8) { die('Le nouveau mot de passe doit contenir au moins 8 caractères.'); }
    $stmt = $pdo->prepare("UPDATE users SET tenant_id=?, name=?, username=?, password=?, role=?, status=? WHERE id=?");
    $stmt->execute([$tenantId, $name, $username, password_hash((string)$_POST['password'], PASSWORD_DEFAULT), $role, $status, $id]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET tenant_id=?, name=?, username=?, role=?, status=? WHERE id=?");
    $stmt->execute([$tenantId, $name, $username, $role, $status, $id]);
}

$pdo->prepare("DELETE FROM user_roles WHERE user_id=?")->execute([$id]);
if ($role === 'super_admin') {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug='super_admin' LIMIT 1");
    $stmt->execute();
    $rid = $stmt->fetchColumn();
    if ($rid) { $roleIds = [(int)$rid]; }
} elseif (!$roleIds) {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug=? AND slug <> 'super_admin' LIMIT 1");
    $stmt->execute([$role]);
    $rid = $stmt->fetchColumn();
    if ($rid) { $roleIds = [(int)$rid]; }
}
$stmt = $pdo->prepare("INSERT INTO user_roles(user_id, role_id) VALUES(?,?)");
foreach ($roleIds as $rid) { $stmt->execute([$id, $rid]); }
$pdo->commit();
redirect('../../views/users/index.php');
