<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/lotteries.php';
require_permission($pdo, 'lotteries.manage');

$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query("SELECT id, name FROM tenants WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
<h1 class="text-2xl font-bold mb-5">Ajouter une lottery</h1>

<form action="../../actions/lotteries/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>

    <?php if (is_super_admin()): ?>
        <label class="block text-sm text-gray-600 mb-1">Tenant / Banque</label>
        <select name="tenant_id" class="w-full border p-3 mb-3 rounded" required>
            <option value="">Choisir tenant</option>
            <?php foreach ($tenants as $tenant): ?>
                <option value="<?= (int)$tenant['id'] ?>"><?= e($tenant['name']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label class="block text-sm text-gray-600 mb-1">Nom lottery</label>
    <input name="name" placeholder="Ex: Florida Midi" class="w-full border p-3 mb-3 rounded" required>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Heure tirage</label>
            <input type="time" name="draw_time" class="w-full border p-3 mb-3 rounded">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Fermeture avant tirage (minutes)</label>
            <input type="number" min="0" name="close_before_minutes" value="10" class="w-full border p-3 mb-3 rounded">
        </div>
    </div>

    <label class="block text-sm text-gray-600 mb-1">Statut des ventes</label>
    <select name="sales_status" class="w-full border p-3 mb-3 rounded">
        <option value="open">Ouverte</option>
        <option value="closed">Fermée</option>
        <option value="drawn">Tirée</option>
    </select>

    <label class="flex items-center gap-2 mb-3">
        <input type="checkbox" name="auto_close_enabled" value="1" checked>
        <span class="text-sm text-gray-700">Fermeture automatique activée</span>
    </label>

    <label class="block text-sm text-gray-600 mb-1">Statut</label>
    <select name="status" class="w-full border p-3 mb-3 rounded">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
    </select>

    <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
