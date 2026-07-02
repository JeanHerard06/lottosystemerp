<?php
require "../../config/database.php";
require "../../includes/header.php";
require "../../includes/sidebar.php";
require "../../includes/topbar.php";
require_once "../../app/Helpers/tenant.php";
require_once "../../app/Helpers/security.php";
require_once "../../app/Helpers/csrf.php";
require_super_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM tenants WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tenant) { die('Tenant introuvable.'); }
?>
<h1 class="text-2xl font-bold mb-5">Modifier tenant</h1>
<form action="../../actions/tenants/update.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$tenant['id'] ?>">
    <input name="name" class="w-full border p-3 rounded mb-3" value="<?= e($tenant['name']) ?>" required>
    <input name="slug" class="w-full border p-3 rounded mb-3" value="<?= e($tenant['slug']) ?>" required>
    <select name="plan" class="w-full border p-3 rounded mb-3">
        <?php foreach(['basic','pro','enterprise'] as $p): ?>
        <option value="<?= $p ?>" <?= $tenant['plan']===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status" class="w-full border p-3 rounded mb-3">
        <?php foreach(['active','suspended','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $tenant['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="expires_at" class="w-full border p-3 rounded mb-3" value="<?= e($tenant['expires_at'] ?? '') ?>">
    <textarea name="notes" class="w-full border p-3 rounded mb-3"><?= e($tenant['notes'] ?? '') ?></textarea>
    <button class="bg-black text-white px-5 py-3 rounded">Mettre à jour</button>
</form>
<?php require "../../includes/footer.php"; ?>
