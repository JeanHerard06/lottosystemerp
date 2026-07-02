<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_permission($pdo, 'notifications.manage');

$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query("SELECT id, name FROM tenants ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}

if (is_super_admin()) {
    $users = $pdo->query("SELECT id, name, username, tenant_id FROM users ORDER BY name LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT id, name, username, tenant_id FROM users WHERE tenant_id=? ORDER BY name");
    $stmt->execute([current_tenant_id()]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1 class="text-2xl font-bold mb-5">Créer notification</h1>
<form action="/actions/notifications/store.php" method="post" class="bg-white p-5 rounded shadow max-w-2xl space-y-3">
    <?= csrf_field() ?>

    <?php if (is_super_admin()): ?>
        <label class="block">
            <span class="text-sm text-gray-600">Tenant</span>
            <select name="tenant_id" class="w-full border p-3 rounded mt-1">
                <option value="">Global / plateforme</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>

    <label class="block">
        <span class="text-sm text-gray-600">Utilisateur spécifique</span>
        <select name="user_id" class="w-full border p-3 rounded mt-1">
            <option value="">Tous les utilisateurs visibles</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['username']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </label>

    <input name="title" class="w-full border p-3 rounded" placeholder="Titre" required maxlength="180">
    <textarea name="message" class="w-full border p-3 rounded" placeholder="Message" rows="5" required></textarea>
    <select name="type" class="w-full border p-3 rounded">
        <option value="info">Info</option>
        <option value="success">Succès</option>
        <option value="warning">Avertissement</option>
        <option value="danger">Urgent</option>
    </select>
    <input name="link_url" class="w-full border p-3 rounded" placeholder="Lien optionnel ex: /views/tirages.php">

    <button class="bg-black text-white px-5 py-3 rounded">Envoyer</button>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
