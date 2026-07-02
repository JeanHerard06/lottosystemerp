<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_permission($pdo, 'agents.manage');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die('Agent invalide.'); }

$params = [$id];
$scopeSql = '';
if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT agency_id FROM supervisors WHERE user_id=? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $agencyId = (int)$stmt->fetchColumn();
    $scopeSql = ' AND a.agency_id = ?';
    $params[] = $agencyId ?: 0;
}

$stmt = $pdo->prepare("SELECT a.*, u.name, u.username, u.status FROM agents a JOIN users u ON u.id=a.user_id WHERE a.id=? {$scopeSql} LIMIT 1");
$stmt->execute($params);
$agent = $stmt->fetch();
if (!$agent) { http_response_code(404); die('Agent introuvable ou non autorisé.'); }

if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT ag.id, ag.name, ag.code, ag.tenant_id FROM supervisors s JOIN agencies ag ON ag.id=s.agency_id AND ag.tenant_id=s.tenant_id WHERE s.user_id=? AND ag.status="active"');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $agencies = $stmt->fetchAll();
} else {
    $agencies = visible_agencies($pdo, true);
}
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Modifier agent</h1>
        <p class="text-gray-500"><?= e($agent['username']) ?></p>
    </div>
    <a href="agents.php" class="bg-gray-200 px-4 py-2 rounded">Retour</a>
</div>

<form action="../actions/agent_update.php" method="POST" class="bg-white p-5 rounded shadow max-w-2xl">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$agent['id'] ?>">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <input name="name" value="<?= e($agent['name']) ?>" placeholder="Nom complet" class="w-full border p-3 rounded" required>
        <input name="username" value="<?= e($agent['username']) ?>" placeholder="Identifiant" class="w-full border p-3 rounded" required>

        <select name="agency_id" class="w-full border p-3 rounded" required>
            <option value="">Choisir agence</option>
            <?php foreach ($agencies as $agency): ?>
                <option value="<?= (int)$agency['id'] ?>" <?= ((int)$agent['agency_id'] === (int)$agency['id']) ? 'selected' : '' ?>>
                    <?= e((is_super_admin() ? ('#'.($agency['tenant_id'] ?? '-') . ' - ') : '') . ($agency['code'] ? $agency['code'] . ' - ' : '') . $agency['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="w-full border p-3 rounded" required>
            <option value="1" <?= ((int)$agent['status'] === 1) ? 'selected' : '' ?>>Actif</option>
            <option value="0" <?= ((int)$agent['status'] === 0) ? 'selected' : '' ?>>Inactif</option>
        </select>

        <input name="phone" value="<?= e($agent['phone']) ?>" placeholder="Téléphone" class="w-full border p-3 rounded">
        <input type="number" step="0.01" name="commission" value="<?= e($agent['commission']) ?>" placeholder="Commission générale %" class="w-full border p-3 rounded">
    </div>

    <h2 class="font-bold mt-6 mb-3">Commissions par jeu</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="number" step="0.01" name="borlette_rate" value="<?= e($agent['borlette_rate']) ?>" placeholder="Borlette %" class="w-full border p-3 rounded">
        <input type="number" step="0.01" name="mariage_rate" value="<?= e($agent['mariage_rate']) ?>" placeholder="Mariage %" class="w-full border p-3 rounded">
        <input type="number" step="0.01" name="lotto3_rate" value="<?= e($agent['lotto3_rate']) ?>" placeholder="Lotto3 %" class="w-full border p-3 rounded">
        <input type="number" step="0.01" name="lotto4_rate" value="<?= e($agent['lotto4_rate']) ?>" placeholder="Lotto4 %" class="w-full border p-3 rounded">
    </div>

    <h2 class="font-bold mt-6 mb-3">Mot de passe</h2>
    <input type="password" name="password" placeholder="Laisser vide pour conserver le mot de passe actuel" class="w-full border p-3 rounded">

    <div class="mt-6 flex gap-3">
        <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
        <a href="agents.php" class="bg-gray-200 px-5 py-3 rounded">Annuler</a>
    </div>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
