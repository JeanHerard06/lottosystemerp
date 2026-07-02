<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_permission($pdo, 'gains.view');

$filter = $_GET['filter'] ?? 'won';
$where = ["g.status = 'won'"];
$params = [];
if ($filter === 'unpaid') {
    $where[] = 'g.is_paid = 0';
} elseif ($filter === 'paid') {
    $where[] = 'g.is_paid = 1';
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
$sql = "SELECT g.*, fd.number_played, fd.play_type, f.fiche_code, f.id AS fiche_id, t.draw_name, t.draw_date, u.name AS agent_name
    FROM gains g
    JOIN fiche_details fd ON fd.id = g.fiche_detail_id
    JOIN fiches f ON f.id = fd.fiche_id
    JOIN tirages t ON t.id = g.tirage_id
    JOIN agents a ON a.id = f.agent_id
    JOIN users u ON u.id = a.user_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY g.id DESC
    LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$gagnants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = array_sum(array_map(fn($g) => (float)$g['amount_won'], $gagnants));

require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Gagnants</h1>
        <p class="text-gray-500">Liste des fiches gagnantes et paiements.</p>
    </div>
</div>

<?php if (isset($_GET['paid'])): ?><div class="bg-green-100 text-green-800 p-4 rounded mb-5">Gain payé avec succès.</div><?php endif; ?>
<?php if (isset($_GET['already_paid'])): ?><div class="bg-yellow-100 text-yellow-800 p-4 rounded mb-5">Ce gain était déjà payé.</div><?php endif; ?>

<div class="flex flex-wrap gap-3 mb-5">
    <a href="?filter=won" class="px-4 py-2 rounded <?= $filter==='won'?'bg-black text-white':'bg-white' ?>">Tous</a>
    <a href="?filter=unpaid" class="px-4 py-2 rounded <?= $filter==='unpaid'?'bg-black text-white':'bg-white' ?>">Non payés</a>
    <a href="?filter=paid" class="px-4 py-2 rounded <?= $filter==='paid'?'bg-black text-white':'bg-white' ?>">Payés</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Tickets</p><h2 class="text-2xl font-bold"><?= count($gagnants) ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Total gains</p><h2 class="text-2xl font-bold"><?= number_format($total, 2) ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Filtre</p><h2 class="text-2xl font-bold"><?= e($filter) ?></h2></div>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full">
    <thead>
        <tr class="bg-gray-200 text-left">
            <th class="p-3">Fiche</th><th class="p-3">Agent</th><th class="p-3">Tirage</th><th class="p-3">Numéro</th><th class="p-3">Jeu</th><th class="p-3">Mise</th><th class="p-3">Gain</th><th class="p-3">Paiement</th><th class="p-3">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($gagnants as $g): ?>
        <tr class="border-b">
            <td class="p-3"><a href="fiches/show.php?id=<?= (int)$g['fiche_id'] ?>" class="text-blue-600"><?= e($g['fiche_code']) ?></a></td>
            <td class="p-3"><?= e($g['agent_name']) ?></td>
            <td class="p-3"><?= e($g['draw_name']) ?><br><span class="text-xs text-gray-500"><?= e($g['draw_date']) ?></span></td>
            <td class="p-3 font-semibold"><?= e($g['number_played']) ?></td>
            <td class="p-3"><?= e($g['play_type']) ?></td>
            <td class="p-3"><?= number_format((float)$g['amount_played'], 2) ?></td>
            <td class="p-3 font-bold"><?= number_format((float)$g['amount_won'], 2) ?></td>
            <td class="p-3"><?= ((int)$g['is_paid'] === 1) ? '<span class="text-green-700 font-semibold">Payé</span>' : '<span class="text-red-700 font-semibold">Non payé</span>' ?></td>
            <td class="p-3">
                <?php if ((int)$g['is_paid'] === 0 && has_permission($pdo, 'gains.pay')): ?>
                    <form action="../actions/gains/pay.php" method="POST" onsubmit="return confirm('Confirmer paiement du gain ?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="gain_id" value="<?= (int)$g['id'] ?>">
                        <button class="bg-green-600 text-white px-3 py-2 rounded text-sm">Payer</button>
                    </form>
                <?php else: ?>-
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$gagnants): ?>
        <tr><td colspan="9" class="p-4 text-center text-gray-500">Aucun gagnant trouvé.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
