<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_permission($pdo, 'agencies.manage');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT ag.*, t.name AS tenant_name FROM agencies ag LEFT JOIN tenants t ON t.id=ag.tenant_id WHERE ag.id=? LIMIT 1');
$stmt->execute([$id]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($agency ?: null, 'agence');
?>
<h1 class="text-2xl font-bold mb-5">Modifier agence</h1>
<form action="../../actions/agencies/update.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$agency['id'] ?>">
    <?php if (is_super_admin()): ?>
        <div class="bg-gray-100 p-3 rounded mb-3">Tenant: <strong><?= e($agency['tenant_name'] ?? '-') ?></strong></div>
    <?php endif; ?>
    <input name="code" value="<?= e($agency['code']) ?>" class="w-full border p-3 mb-3 rounded" required>
    <input name="name" value="<?= e($agency['name']) ?>" class="w-full border p-3 mb-3 rounded" required>
    <input name="phone" value="<?= e($agency['phone']) ?>" class="w-full border p-3 mb-3 rounded">
    <textarea name="address" class="w-full border p-3 mb-3 rounded"><?= e($agency['address']) ?></textarea>
    <select name="status" class="w-full border p-3 mb-3 rounded">
        <option value="active" <?= $agency['status']==='active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $agency['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
    </select>
    <button class="bg-black text-white px-5 py-3 rounded">Mettre à jour</button>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
