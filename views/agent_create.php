<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_permission($pdo, 'agents.manage');

if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT ag.id, ag.name, ag.code, ag.tenant_id FROM supervisors s JOIN agencies ag ON ag.id=s.agency_id AND ag.tenant_id=s.tenant_id WHERE s.user_id=? AND ag.status="active"');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $agencies = $stmt->fetchAll();
} else {
    $agencies = visible_agencies($pdo, true);
}
?>
<h1 class="text-2xl font-bold mb-5">Ajouter un agent</h1>
<form action="../actions/agent_store.php" method="POST" class="bg-white p-5 rounded shadow max-w-lg">
    <?= csrf_field() ?>
    <input name="name" placeholder="Nom complet" class="w-full border p-3 mb-3 rounded" required>
    <input name="username" placeholder="Identifiant" class="w-full border p-3 mb-3 rounded" required>
    <select name="agency_id" class="w-full border p-3 mb-3 rounded" required>
        <option value="">Choisir agence</option>
        <?php foreach ($agencies as $agency): ?>
            <option value="<?= (int)$agency['id'] ?>"><?= e((is_super_admin() ? ('#'.($agency['tenant_id'] ?? '-') . ' - ') : '') . $agency['code'] . ' - ' . $agency['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <input name="phone" placeholder="Téléphone" class="w-full border p-3 mb-3 rounded">
    <input type="number" step="0.01" name="commission" placeholder="Commission %" class="w-full border p-3 mb-3 rounded">
    <input type="password" name="password" placeholder="Mot de passe" class="w-full border p-3 mb-3 rounded" required>
    <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
