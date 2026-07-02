<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_permission($pdo, 'agencies.manage');
$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query("SELECT id, name FROM tenants WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<h1 class="text-2xl font-bold mb-5">Ajouter une agence</h1>
<form action="../../actions/agencies/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>
    <?php if (is_super_admin()): ?>
        <select name="tenant_id" class="w-full border p-3 mb-3 rounded" required>
            <option value="">Choisir tenant</option>
            <?php foreach ($tenants as $tenant): ?>
                <option value="<?= (int)$tenant['id'] ?>"><?= e($tenant['name']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <input name="code" placeholder="Code agence ex: PV" class="w-full border p-3 mb-3 rounded" required>
    <input name="name" placeholder="Nom agence" class="w-full border p-3 mb-3 rounded" required>
    <input name="phone" placeholder="Téléphone" class="w-full border p-3 mb-3 rounded">
    <textarea name="address" placeholder="Adresse" class="w-full border p-3 mb-3 rounded"></textarea>
    <select name="status" class="w-full border p-3 mb-3 rounded">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
    <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
