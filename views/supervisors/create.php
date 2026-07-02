<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_permission($pdo, 'supervisors.manage');
$agencies = visible_agencies($pdo, true);
?>
<h1 class="text-2xl font-bold mb-5">Ajouter superviseur</h1>
<form action="../../actions/supervisors/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>
    <input name="name" placeholder="Nom complet" class="w-full border p-3 mb-3 rounded" required>
    <input name="username" placeholder="Identifiant" class="w-full border p-3 mb-3 rounded" required>
    <select name="agency_id" class="w-full border p-3 mb-3 rounded" required>
        <option value="">Agence</option>
        <?php foreach ($agencies as $agency): ?>
            <option value="<?= (int)$agency['id'] ?>"><?= e((is_super_admin() ? ('#'.($agency['tenant_id'] ?? '-') . ' - ') : '') . $agency['code'] . ' - ' . $agency['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="password" name="password" placeholder="Mot de passe" class="w-full border p-3 mb-3 rounded" required>
    <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
