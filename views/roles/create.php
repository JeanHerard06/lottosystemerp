<?php
require "../../config/database.php"; require "../../includes/header.php"; require_once "../../app/Helpers/permissions.php"; require_permission($pdo,'roles.manage'); require "../../includes/sidebar.php"; require "../../includes/topbar.php";
$perms=visible_permissions($pdo);
?>
<h1 class="text-2xl font-bold mb-5">Nouveau rôle</h1><form action="../../actions/roles/store.php" method="post" class="bg-white rounded-xl shadow p-5 max-w-2xl"><?= csrf_field() ?><input name="name" class="w-full border p-3 rounded mb-3" placeholder="Nom" required><input name="slug" class="w-full border p-3 rounded mb-3" placeholder="Slug" required><p class="text-sm text-gray-500 mb-3">Le slug super_admin est protégé et ne peut pas être créé depuis un tenant.</p><div class="grid md:grid-cols-2 gap-2 mb-4"><?php foreach($perms as $p): ?><label class="border rounded p-2"><input type="checkbox" name="permission_ids[]" value="<?= (int)$p['id'] ?>"> <?= e($p['name']) ?></label><?php endforeach; ?></div><button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button></form>
<?php require "../../includes/footer.php"; ?>
