<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_permission($pdo, 'tirages.manage');

$tenantId = tenant_value();
if (in_array(current_user_role(), ['admin','super_admin'], true) || !$tenantId) {
    $lotteries = $pdo->query("SELECT * FROM lotteries WHERE status = 1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM lotteries WHERE status = 1 AND (tenant_id = ? OR tenant_id IS NULL) ORDER BY name");
    $stmt->execute([$tenantId]);
    $lotteries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
?>
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-bold">Ajouter Tirage</h1>
    <a href="tirages.php" class="bg-gray-800 text-white px-4 py-2 rounded">Retour</a>
</div>

<form action="../actions/tirage_store.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>

    <label class="block text-sm text-gray-600 mb-1">Lotterie</label>
    <select name="lottery_id" class="w-full border p-3 mb-3 rounded">
        <option value="">Aucune / Générale</option>
        <?php foreach ($lotteries as $l): ?>
            <option value="<?= (int)$l['id'] ?>"><?= e($l['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label class="block text-sm text-gray-600 mb-1">Nom du tirage</label>
    <input name="draw_name" placeholder="Ex: Florida Midi" class="w-full border p-3 mb-3 rounded" required>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-sm text-gray-600 mb-1">1er Lot</label>
            <input name="first_number" placeholder="Ex: 17" class="w-full border p-3 mb-3 rounded" required>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">2e Lot</label>
            <input name="second_number" placeholder="Ex: 25" class="w-full border p-3 mb-3 rounded">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">3e Lot</label>
            <input name="third_number" placeholder="Ex: 88" class="w-full border p-3 mb-3 rounded">
        </div>
    </div>

    <label class="block text-sm text-gray-600 mb-1">Date</label>
    <input type="date" name="draw_date" value="<?= date('Y-m-d') ?>" class="w-full border p-3 mb-5 rounded" required>

    <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
