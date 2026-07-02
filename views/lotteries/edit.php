<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/lotteries.php';
require_permission($pdo, 'lotteries.manage');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM lotteries WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$lottery = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($lottery, 'lottery');

$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query("SELECT id, name FROM tenants WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
<h1 class="text-2xl font-bold mb-5">Modifier lottery</h1>

<form action="../../actions/lotteries/update.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$lottery['id'] ?>">

    <?php if (is_super_admin()): ?>
        <label class="block text-sm text-gray-600 mb-1">Tenant / Banque</label>
        <select name="tenant_id" class="w-full border p-3 mb-3 rounded" required>
            <?php foreach ($tenants as $tenant): ?>
                <option value="<?= (int)$tenant['id'] ?>" <?= (int)$tenant['id'] === (int)$lottery['tenant_id'] ? 'selected' : '' ?>><?= e($tenant['name']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label class="block text-sm text-gray-600 mb-1">Nom lottery</label>
    <input name="name" value="<?= e($lottery['name']) ?>" class="w-full border p-3 mb-3 rounded" required>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Heure tirage</label>
            <input type="time" name="draw_time" value="<?= e(substr((string)($lottery['draw_time'] ?? ''), 0, 5)) ?>" class="w-full border p-3 mb-3 rounded">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Fermeture avant tirage (minutes)</label>
            <input type="number" min="0" name="close_before_minutes" value="<?= (int)($lottery['close_before_minutes'] ?? 10) ?>" class="w-full border p-3 mb-3 rounded">
        </div>
    </div>

    <label class="block text-sm text-gray-600 mb-1">Statut des ventes</label>
    <select name="sales_status" class="w-full border p-3 mb-3 rounded">
        <option value="open" <?= ($lottery['sales_status'] ?? 'open') === 'open' ? 'selected' : '' ?>>Ouverte</option>
        <option value="closed" <?= ($lottery['sales_status'] ?? 'open') === 'closed' ? 'selected' : '' ?>>Fermée</option>
        <option value="drawn" <?= ($lottery['sales_status'] ?? 'open') === 'drawn' ? 'selected' : '' ?>>Tirée</option>
    </select>

    <label class="flex items-center gap-2 mb-3">
        <input type="checkbox" name="auto_close_enabled" value="1" <?= (int)($lottery['auto_close_enabled'] ?? 1) === 1 ? 'checked' : '' ?>>
        <span class="text-sm text-gray-700">Fermeture automatique activée</span>
    </label>

    <label class="block text-sm text-gray-600 mb-1">Statut</label>
    <select name="status" class="w-full border p-3 mb-3 rounded">
        <option value="1" <?= (int)$lottery['status'] === 1 ? 'selected' : '' ?>>Active</option>
        <option value="0" <?= (int)$lottery['status'] === 0 ? 'selected' : '' ?>>Inactive</option>
    </select>

    <button class="bg-black text-white px-5 py-3 rounded">Mettre à jour</button>

</form>

<form action="../../actions/lotteries/delete.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl mt-5" onsubmit="return confirm('Supprimer cette lottery ? Cette action est impossible si elle contient des tirages ou fiches.');">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$lottery['id'] ?>">
    <p class="text-sm text-gray-600 mb-3">Suppression autorisée seulement si aucune fiche ni tirage n'est rattaché.</p>
    <button class="bg-red-600 text-white px-5 py-3 rounded">Supprimer</button>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
