<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_permission($pdo, 'tirages.manage');

$where = [];
$params = [];
if (!in_array(current_user_role(), ['admin','super_admin'], true)) {
    $tenantId = tenant_value();
    if ($tenantId) {
        $where[] = 't.tenant_id = ?';
        $params[] = $tenantId;
    } else {
        $where[] = '1=0';
    }
}
$sql = "SELECT t.*, l.name AS lottery_name,
    (SELECT COUNT(*) FROM gains g WHERE g.tirage_id = t.id AND g.status = 'won') AS winners_count,
    (SELECT COALESCE(SUM(g.amount_won),0) FROM gains g WHERE g.tirage_id = t.id AND g.status = 'won') AS total_won
    FROM tirages t
    LEFT JOIN lotteries l ON l.id = t.lottery_id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY t.draw_date DESC, t.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tirages = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Tirages</h1>
        <p class="text-gray-500">Résultats, calcul automatique et suivi des gagnants.</p>
    </div>
    <a href="tirage_create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Ajouter tirage</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full">
    <thead>
        <tr class="bg-gray-200 text-left">
            <th class="p-3">Tirage</th>
            <th class="p-3">Lotterie</th>
            <th class="p-3">Résultats</th>
            <th class="p-3">Date</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Gagnants</th>
            <th class="p-3">Total gains</th>
            <th class="p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tirages as $t): ?>
        <tr class="border-b">
            <td class="p-3 font-semibold"><?= e($t['draw_name']) ?></td>
            <td class="p-3"><?= e($t['lottery_name'] ?? '-') ?></td>
            <td class="p-3">
                <span class="font-bold"><?= e($t['first_number']) ?></span>
                <?= $t['second_number'] ? ' / ' . e($t['second_number']) : '' ?>
                <?= $t['third_number'] ? ' / ' . e($t['third_number']) : '' ?>
            </td>
            <td class="p-3"><?= e($t['draw_date']) ?></td>
            <td class="p-3">
                <span class="px-2 py-1 rounded text-xs <?= $t['status']==='processed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                    <?= e($t['status']) ?>
                </span>
            </td>
            <td class="p-3"><?= (int)$t['winners_count'] ?></td>
            <td class="p-3"><?= number_format((float)$t['total_won'], 2) ?></td>
            <td class="p-3">
                <a href="tirages/show.php?id=<?= (int)$t['id'] ?>" class="bg-black text-white px-3 py-2 rounded text-sm">Voir</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$tirages): ?>
        <tr><td colspan="8" class="p-4 text-center text-gray-500">Aucun tirage trouvé.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
