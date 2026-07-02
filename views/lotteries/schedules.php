<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';

require_permission($pdo, 'lottery_schedules.manage');

$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query('SELECT id, name FROM tenants ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
}
$tenantFilter = is_super_admin() && !empty($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : current_tenant_id();

$params = [];
$where = [];
if ($tenantFilter) { $where[] = 'l.tenant_id=?'; $params[] = $tenantFilter; }
elseif (!is_super_admin()) { $where[] = '1=0'; }

$sqlLot = 'SELECT l.id, l.name, l.tenant_id, t.name AS tenant_name FROM lotteries l LEFT JOIN tenants t ON t.id=l.tenant_id';
if ($where) { $sqlLot .= ' WHERE ' . implode(' AND ', $where); }
$sqlLot .= ' ORDER BY l.name';
$stmt = $pdo->prepare($sqlLot);
$stmt->execute($params);
$lotteries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT s.*, l.name AS lottery_name, t.name AS tenant_name
        FROM lottery_schedules s
        JOIN lotteries l ON l.id=s.lottery_id
        JOIN tenants t ON t.id=s.tenant_id";
$params = [];
if ($tenantFilter) { $sql .= ' WHERE s.tenant_id=?'; $params[] = $tenantFilter; }
elseif (!is_super_admin()) { $sql .= ' WHERE 1=0'; }
$sql .= ' ORDER BY t.name, l.name, s.day_of_week, s.draw_time';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
$days = [null => 'Tous les jours', 0 => 'Dimanche', 1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Horaires lotteries</h1>
        <p class="text-gray-500">Fenêtres de vente, fermeture automatique et heure de tirage.</p>
    </div>
</div>

<?php if (is_super_admin()): ?>
<form method="GET" class="bg-white p-4 rounded shadow mb-5 flex gap-3 items-end">
    <div class="flex-1">
        <label class="block text-sm text-gray-600 mb-1">Tenant</label>
        <select name="tenant_id" class="form-control w-full border p-3 rounded" onchange="this.form.submit()">
            <option value="">Tous</option>
            <?php foreach ($tenants as $t): ?>
                <option value="<?= (int)$t['id'] ?>" <?= (int)$tenantFilter === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</form>
<?php endif; ?>

<form action="/actions/lotteries/schedules_store.php" method="POST" class="bg-white p-5 rounded shadow mb-6">
    <?= csrf_field() ?>
    <h2 class="font-bold text-lg mb-3">Ajouter horaire</h2>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <select name="lottery_id" class="form-control border p-3 rounded md:col-span-2" required>
            <option value="">Lottery</option>
            <?php foreach ($lotteries as $l): ?>
                <option value="<?= (int)$l['id'] ?>"><?= e($l['lottery_name'] ?? $l['name'] ?? '') ?><?= is_super_admin() ? ' — ' . e($l['tenant_name'] ?? '') : '' ?></option>
            <?php endforeach; ?>
        </select>
        <select name="day_of_week" class="form-control border p-3 rounded">
            <option value="">Tous les jours</option>
            <?php for ($i=0; $i<=6; $i++): ?><option value="<?= $i ?>"><?= e($days[$i]) ?></option><?php endfor; ?>
        </select>
        <input type="time" name="draw_time" class="form-control border p-3 rounded" required>
        <input type="number" name="close_before_minutes" value="10" min="0" class="form-control border p-3 rounded" required>
        <button class="btn bg-black text-white px-4 py-3 rounded">Ajouter</button>
    </div>
</form>

<table class="w-full bg-white rounded shadow responsive-table">
    <thead><tr class="bg-gray-200 text-left"><th class="p-3">Tenant</th><th class="p-3">Lottery</th><th class="p-3">Jour</th><th class="p-3">Tirage</th><th class="p-3">Fermeture avant</th><th class="p-3">Status</th><th class="p-3">Action</th></tr></thead>
    <tbody>
        <?php foreach ($schedules as $s): ?>
        <tr class="border-b">
            <td data-label="Tenant" class="p-3"><?= e($s['tenant_name']) ?></td>
            <td data-label="Lottery" class="p-3"><?= e($s['lottery_name']) ?></td>
            <td data-label="Jour" class="p-3"><?= e($days[$s['day_of_week']] ?? 'Tous les jours') ?></td>
            <td data-label="Tirage" class="p-3 font-semibold"><?= e(substr((string)$s['draw_time'],0,5)) ?></td>
            <td data-label="Fermeture" class="p-3"><?= (int)$s['close_before_minutes'] ?> min</td>
            <td data-label="Status" class="p-3"><?= e($s['status']) ?></td>
            <td data-label="Action" class="p-3">
                <form action="/actions/lotteries/schedules_delete.php" method="POST" onsubmit="return confirm('Supprimer cet horaire ?')">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button class="text-red-600">Supprimer</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
