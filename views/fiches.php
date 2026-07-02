<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/fiches.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_permission($pdo, 'fiches.view');
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';

$where = [];
$params = [];
$status = $_GET['status'] ?? '';
$date = $_GET['date'] ?? '';
$lottery = $_GET['lottery_id'] ?? '';

if ($status !== '') {
    $where[] = 'f.status = ?';
    $params[] = $status;
}
if ($date !== '') {
    $where[] = 'DATE(f.created_at) = ?';
    $params[] = $date;
}
if ($lottery !== '') {
    $where[] = 'f.lottery_id = ?';
    $params[] = (int)$lottery;
}

if (!in_array(current_user_role(), ['admin','super_admin'], true)) {
    $tenantId = tenant_value();
    if ($tenantId) {
        $where[] = 'f.tenant_id = ?';
        $params[] = $tenantId;
    } else {
        $where[] = '1=0';
    }
}

if (current_user_role() === 'agent') {
    $agent = current_agent($pdo);
    $where[] = 'f.agent_id = ?';
    $params[] = $agent ? (int)$agent['id'] : 0;
} elseif (current_user_role() === 'superviseur') {
    $stmt = $pdo->prepare('SELECT agency_id FROM supervisors WHERE user_id = ? LIMIT 1');
    $stmt->execute([current_user_id()]);
    $agencyId = (int)$stmt->fetchColumn();
    $where[] = 'a.agency_id = ?';
    $params[] = $agencyId;
}

$sql = "SELECT f.*, u.name AS agent_name, l.name AS lottery_name, a.agency_id
        FROM fiches f
        JOIN agents a ON a.id = f.agent_id
        JOIN users u ON u.id = a.user_id
        LEFT JOIN lotteries l ON l.id = f.lottery_id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY f.id DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (in_array(current_user_role(), ['admin','super_admin'], true) || !tenant_value()) {
    $lotteries = $pdo->query('SELECT id, name FROM lotteries WHERE status = 1 ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare('SELECT id, name FROM lotteries WHERE status = 1 AND (tenant_id = ? OR tenant_id IS NULL) ORDER BY name');
    $stmt->execute([tenant_value()]);
    $lotteries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-bold">Fiches</h1>
    <?php if (has_permission($pdo, 'fiches.create')): ?>
        <a href="fiche_create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Nouvelle fiche</a>
    <?php endif; ?>
</div>

<form method="GET" class="bg-white p-4 rounded shadow mb-5 flex flex-col md:flex-row gap-3">
    <input type="date" name="date" value="<?= e($date) ?>" class="border p-3 rounded">
    <select name="status" class="border p-3 rounded">
        <option value="">Tous statuts</option>
        <?php foreach (['pending','won','lost','paid','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="lottery_id" class="border p-3 rounded">
        <option value="">Toutes loteries</option>
        <?php foreach ($lotteries as $l): ?>
            <option value="<?= (int)$l['id'] ?>" <?= (string)$lottery === (string)$l['id'] ? 'selected' : '' ?>><?= e($l['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="bg-black text-white px-5 py-3 rounded">Filtrer</button>
</form>

<div class="overflow-x-auto bg-white rounded shadow">
<table class="w-full">
    <thead>
    <tr class="bg-gray-200 text-left">
        <th class="p-3">Code</th><th class="p-3">Agent</th><th class="p-3">Tirage</th><th class="p-3">Total</th><th class="p-3">Gain</th><th class="p-3">Statut</th><th class="p-3">Date</th><th class="p-3">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($fiches as $fiche): ?>
        <tr class="border-b">
            <td class="p-3 font-semibold"><?= e($fiche['fiche_code']) ?></td>
            <td class="p-3"><?= e($fiche['agent_name']) ?></td>
            <td class="p-3"><?= e($fiche['lottery_name'] ?? '-') ?></td>
            <td class="p-3"><?= number_format((float)$fiche['total_amount'], 2) ?></td>
            <td class="p-3"><?= number_format((float)$fiche['gain_amount'], 2) ?></td>
            <td class="p-3"><span class="px-2 py-1 rounded text-xs bg-gray-100"><?= e($fiche['status']) ?></span></td>
            <td class="p-3"><?= e($fiche['created_at']) ?></td>
            <td class="p-3 whitespace-nowrap">
                <a href="fiches/show.php?id=<?= (int)$fiche['id'] ?>" class="bg-blue-600 text-white px-3 py-2 rounded">Voir</a>
                <a href="../actions/print_ticket.php?id=<?= (int)$fiche['id'] ?>" target="_blank" class="bg-green-600 text-white px-3 py-2 rounded">Ticket</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$fiches): ?>
        <tr><td colspan="8" class="p-4 text-center text-gray-500">Aucune fiche trouvée.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
