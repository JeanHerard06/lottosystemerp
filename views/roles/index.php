<?php
require "../../config/database.php";
require "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'roles.manage');
require "../../includes/sidebar.php";
require "../../includes/topbar.php";
$roleWhere = is_super_admin() ? '' : "WHERE r.slug <> 'super_admin'";
$roles=$pdo->query("SELECT r.*, COUNT(rp.permission_id) permission_count FROM roles r LEFT JOIN role_permissions rp ON rp.role_id=r.id $roleWhere GROUP BY r.id ORDER BY r.id")->fetchAll();
?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Rôles</h1><a href="create.php" class="bg-black text-white px-4 py-2 rounded">+ Rôle</a></div>
<table class="w-full bg-white rounded-xl shadow"><thead><tr class="bg-gray-100 text-left"><th class="p-3">Nom</th><th class="p-3">Slug</th><th class="p-3">Permissions</th><th class="p-3"></th></tr></thead><tbody>
<?php foreach($roles as $r): ?><tr class="border-b"><td class="p-3"><?= e($r['name']) ?></td><td class="p-3"><?= e($r['slug']) ?></td><td class="p-3"><?= (int)$r['permission_count'] ?></td><td class="p-3"><a class="text-blue-600" href="edit.php?id=<?= (int)$r['id'] ?>">Modifier</a></td></tr><?php endforeach; ?>
</tbody></table>
<?php require "../../includes/footer.php"; ?>
