<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_permission($pdo, 'supervisors.manage');
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT s.*, u.name, u.username, u.status FROM supervisors s JOIN users u ON u.id=s.user_id AND u.tenant_id=s.tenant_id WHERE s.id=? LIMIT 1");
$stmt->execute([$id]);
$supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($supervisor ?: null, 'superviseur');
$agencies = visible_agencies($pdo, true);
?>
<h1 class="text-2xl font-bold mb-5">Modifier superviseur</h1>
<form action="../../actions/supervisors/update.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$supervisor['id'] ?>">
    <input type="hidden" name="user_id" value="<?= (int)$supervisor['user_id'] ?>">
    <input name="name" value="<?= e($supervisor['name']) ?>" class="w-full border p-3 mb-3 rounded" required>
    <input name="username" value="<?= e($supervisor['username']) ?>" class="w-full border p-3 mb-3 rounded" required>
    <select name="agency_id" class="w-full border p-3 mb-3 rounded" required>
        <option value="">Agence</option>
        <?php foreach ($agencies as $agency): ?>
            <option value="<?= (int)$agency['id'] ?>" <?= (int)$supervisor['agency_id'] === (int)$agency['id'] ? 'selected' : '' ?>><?= e((is_super_admin() ? ('#'.($agency['tenant_id'] ?? '-') . ' - ') : '') . $agency['code'] . ' - ' . $agency['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="w-full border p-3 mb-3 rounded">
        <option value="1" <?= (int)$supervisor['status'] === 1 ? 'selected' : '' ?>>Actif</option>
        <option value="0" <?= (int)$supervisor['status'] === 0 ? 'selected' : '' ?>>Inactif</option>
    </select>
    <input type="password" name="password" placeholder="Nouveau mot de passe (laisser vide)" class="w-full border p-3 mb-3 rounded">
    <button class="bg-black text-white px-5 py-3 rounded">Mettre à jour</button>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
