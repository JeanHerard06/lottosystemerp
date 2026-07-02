<?php
require "../../config/database.php"; require "../../includes/header.php"; require_once "../../app/Helpers/permissions.php"; require_permission($pdo,'roles.manage'); require "../../includes/sidebar.php"; require "../../includes/topbar.php";
$permissions=$pdo->query("SELECT * FROM permissions ORDER BY module, name")->fetchAll();
?>
<h1 class="text-2xl font-bold mb-5">Permissions</h1>
<table class="w-full bg-white rounded-xl shadow"><thead><tr class="bg-gray-100 text-left"><th class="p-3">Nom</th><th class="p-3">Slug</th><th class="p-3">Module</th></tr></thead><tbody>
<?php foreach($permissions as $p): ?><tr class="border-b"><td class="p-3"><?= e($p['name']) ?></td><td class="p-3"><?= e($p['slug']) ?></td><td class="p-3"><?= e($p['module']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require "../../includes/footer.php"; ?>
