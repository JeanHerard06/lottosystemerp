<?php
require "../../config/database.php";
require "../../includes/header.php";
require "../../includes/sidebar.php";
require "../../includes/topbar.php";
require_once "../../app/Helpers/tenant.php";
require_once "../../app/Helpers/permissions.php";
require_once "../../app/Helpers/security.php";
require_super_admin();

$tenants = $pdo->query("SELECT * FROM tenants ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold">Tenants / Banques</h1>
        <p class="text-gray-500">Gestion SaaS multi-bank</p>
    </div>
    <a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Nouveau tenant</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full text-left">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-3">Nom</th><th class="p-3">Slug</th><th class="p-3">Plan</th><th class="p-3">Statut</th><th class="p-3">Expiration</th><th class="p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($tenants as $t): ?>
        <tr class="border-t">
            <td class="p-3 font-semibold"><?= e($t['name']) ?></td>
            <td class="p-3"><?= e($t['slug']) ?></td>
            <td class="p-3"><?= e($t['plan']) ?></td>
            <td class="p-3"><?= e($t['status']) ?></td>
            <td class="p-3"><?= e($t['expires_at'] ?? '-') ?></td>
            <td class="p-3"><a class="text-blue-600" href="edit.php?id=<?= (int)$t['id'] ?>">Modifier</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require "../../includes/footer.php"; ?>
