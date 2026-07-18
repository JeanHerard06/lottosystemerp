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

$headerActions = [];
if (has_permission($pdo, 'fiches.create')) {
    $headerActions[] = ['label' => 'Nouvelle fiche', 'href' => 'fiche_create.php', 'class' => 'ui-btn ui-btn-warning', 'icon' => '+'];
}
ui_page_header('Fiches', 'Consultez, filtrez et imprimez les tickets enregistrés.', $headerActions);
?>

<div class="ui-filter-bar">
    <form method="GET" class="ui-filter-form" data-no-responsive-filter="1">
        <label class="min-w-[10rem] flex-1">
            <span class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Date</span>
            <input type="date" name="date" value="<?= e($date) ?>" class="form-control">
        </label>
        <label class="min-w-[10rem] flex-1">
            <span class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Statut</span>
            <select name="status" class="form-control">
                <option value="">Tous statuts</option>
                <?php foreach (['pending','won','lost','paid','cancelled'] as $s): ?>
                    <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="min-w-[12rem] flex-1">
            <span class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Lottery</span>
            <select name="lottery_id" class="form-control">
                <option value="">Toutes lotteries</option>
                <?php foreach ($lotteries as $l): ?>
                    <option value="<?= (int)$l['id'] ?>" <?= (string)$lottery === (string)$l['id'] ? 'selected' : '' ?>><?= e($l['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="ui-btn ui-btn-primary" type="submit">Filtrer</button>
    </form>
</div>

<div class="ui-table-panel">
    <table class="w-full">
        <thead>
        <tr class="text-left">
            <th class="p-3">Code</th><th class="p-3">Agent</th><th class="p-3">Tirage</th><th class="p-3">Total</th><th class="p-3">Gain</th><th class="p-3">Statut</th><th class="p-3">Date</th><th class="p-3">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($fiches as $fiche): ?>
            <tr class="border-b">
                <td class="p-3 font-semibold"><?= e($fiche['fiche_code']) ?></td>
                <td class="p-3"><?= e($fiche['agent_name']) ?></td>
                <td class="p-3"><?= e($fiche['lottery_name'] ?? '-') ?></td>
                <td class="p-3 font-semibold"><?= ui_money((float)$fiche['total_amount']) ?></td>
                <td class="p-3"><?= ui_money((float)$fiche['gain_amount']) ?></td>
                <td class="p-3"><?= ui_status_badge((string)$fiche['status']) ?></td>
                <td class="p-3"><?= e($fiche['created_at']) ?></td>
                <td class="p-3 whitespace-nowrap">
                    <div class="flex gap-2 flex-wrap justify-end">
                        <?= ui_action_link('Voir', 'fiches/show.php?id=' . (int)$fiche['id'], 'primary', '↗') ?>
                        <a href="../actions/print_ticket.php?id=<?= (int)$fiche['id'] ?>" target="_blank" class="ui-btn ui-btn-success ui-btn-sm"><span aria-hidden="true">🖨</span><span>Ticket</span></a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$fiches): ?>
            <tr><td colspan="8"><?php ui_empty_state('Aucune fiche trouvée', 'Modifiez les filtres ou créez une nouvelle fiche.', '🎟'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
