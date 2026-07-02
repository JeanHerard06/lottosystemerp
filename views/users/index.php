<?php
require "../../config/database.php";
require "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'users.manage');
require "../../includes/sidebar.php";
require "../../includes/topbar.php";

$params = [];
$where = "WHERE 1=1";
if (!is_super_admin()) {
    $where .= " AND u.tenant_id = ? AND u.role <> 'super_admin'";
    $params[] = current_tenant_id();
}

$stmt = $pdo->prepare("\n    SELECT u.*, t.name AS tenant_name, GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ') AS roles\n    FROM users u\n    LEFT JOIN tenants t ON t.id = u.tenant_id\n    LEFT JOIN user_roles ur ON ur.user_id = u.id\n    LEFT JOIN roles r ON r.id = ur.role_id\n    $where\n    GROUP BY u.id, t.name\n    ORDER BY u.id DESC\n");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold">Utilisateurs</h1>
        <p class="text-gray-500">Gestion des comptes. Les tenants ne peuvent pas attribuer super_admin.</p>
    </div>
    <a href="create.php" class="bg-black text-white px-4 py-2 rounded">+ Nouvel utilisateur</a>
</div>
<table class="w-full bg-white rounded-xl shadow overflow-hidden">
<thead><tr class="bg-gray-100 text-left"><th class="p-3">Nom</th><?php if(is_super_admin()): ?><th class="p-3">Tenant</th><?php endif; ?><th class="p-3">Identifiant</th><th class="p-3">Rôle système</th><th class="p-3">Rôles RBAC</th><th class="p-3">Statut</th><th class="p-3">Action</th></tr></thead>
<tbody>
<?php foreach($users as $u): ?>
<tr class="border-b">
<td class="p-3"><?= e($u['name']) ?></td>
<?php if(is_super_admin()): ?><td class="p-3"><?= e($u['tenant_name'] ?: 'Plateforme') ?></td><?php endif; ?>
<td class="p-3"><?= e($u['username']) ?></td>
<td class="p-3"><?= e($u['role']) ?></td>
<td class="p-3"><?= e($u['roles'] ?: '-') ?></td>
<td class="p-3"><?= $u['status'] ? 'Actif' : 'Inactif' ?></td>
<td class="p-3"><a class="text-blue-600" href="edit.php?id=<?= (int)$u['id'] ?>">Modifier</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require "../../includes/footer.php"; ?>
