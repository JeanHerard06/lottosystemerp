<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_permission($pdo, 'tirages.manage');

$id = (int)($_GET['id'] ?? 0);
$whereTenant = '';
$params = [$id];
if (!in_array(current_user_role(), ['admin','super_admin'], true)) {
    $tenantId = tenant_value();
    if ($tenantId) {
        $whereTenant = ' AND t.tenant_id = ?';
        $params[] = $tenantId;
    } else {
        $whereTenant = ' AND 1=0';
    }
}
$stmt = $pdo->prepare("SELECT t.*, l.name AS lottery_name FROM tirages t LEFT JOIN lotteries l ON l.id=t.lottery_id WHERE t.id=?" . $whereTenant . " LIMIT 1");
$stmt->execute($params);
$tirage = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$tirage) {
    http_response_code(404);
    die('Tirage introuvable.');
}

$stmt = $pdo->prepare("SELECT g.*, fd.number_played, fd.play_type, f.fiche_code, f.id AS fiche_id, u.name AS agent_name
    FROM gains g
    JOIN fiche_details fd ON fd.id = g.fiche_detail_id
    JOIN fiches f ON f.id = fd.fiche_id
    JOIN agents a ON a.id = f.agent_id
    JOIN users u ON u.id = a.user_id
    WHERE g.tirage_id = ?
    ORDER BY g.status DESC, g.amount_won DESC, g.id DESC");
$stmt->execute([$id]);
$gains = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalWon = array_sum(array_map(fn($g) => (float)$g['amount_won'], array_filter($gains, fn($g) => $g['status']==='won')));
$winners = count(array_filter($gains, fn($g) => $g['status']==='won'));

require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold"><?= e($tirage['draw_name']) ?></h1>
        <p class="text-gray-500">Détail tirage et calcul des gagnants.</p>
    </div>
    <a href="../tirages.php" class="bg-gray-800 text-white px-4 py-2 rounded">Retour</a>
</div>

<?php if (isset($_GET['created'])): ?><div class="bg-green-100 text-green-800 p-4 rounded mb-5">Tirage créé avec succès.</div><?php endif; ?>
<?php if (isset($_GET['calculated'])): ?><div class="bg-green-100 text-green-800 p-4 rounded mb-5">Calcul des gains effectué avec succès.</div><?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-5">
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Lotterie</p><h2 class="font-bold"><?= e($tirage['lottery_name'] ?? '-') ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Résultats</p><h2 class="font-bold"><?= e($tirage['first_number']) ?><?= $tirage['second_number'] ? ' / '.e($tirage['second_number']) : '' ?><?= $tirage['third_number'] ? ' / '.e($tirage['third_number']) : '' ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Date</p><h2 class="font-bold"><?= e($tirage['draw_date']) ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Gagnants</p><h2 class="font-bold"><?= $winners ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Total gains</p><h2 class="font-bold"><?= number_format($totalWon, 2) ?></h2></div>
</div>

<?php if (has_permission($pdo, 'gains.calculate')): ?>
<form action="../../actions/tirages/calculate.php" method="POST" class="mb-5" onsubmit="return confirm('Relancer le calcul des gains pour ce tirage ?')">
    <?= csrf_field() ?>
    <input type="hidden" name="tirage_id" value="<?= (int)$tirage['id'] ?>">
    <button class="bg-yellow-500 text-white px-5 py-3 rounded">Calculer / Recalculer les gains</button>
</form>
<?php endif; ?>

<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full">
    <thead>
        <tr class="bg-gray-200 text-left">
            <th class="p-3">Fiche</th>
            <th class="p-3">Agent</th>
            <th class="p-3">Jeu</th>
            <th class="p-3">Numéro</th>
            <th class="p-3">Mise</th>
            <th class="p-3">Gain</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Paiement</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($gains as $g): ?>
        <tr class="border-b">
            <td class="p-3"><a class="text-blue-600" href="../fiches/show.php?id=<?= (int)$g['fiche_id'] ?>"><?= e($g['fiche_code']) ?></a></td>
            <td class="p-3"><?= e($g['agent_name']) ?></td>
            <td class="p-3"><?= e($g['play_type']) ?></td>
            <td class="p-3 font-semibold"><?= e($g['number_played']) ?></td>
            <td class="p-3"><?= number_format((float)$g['amount_played'], 2) ?></td>
            <td class="p-3 font-bold"><?= number_format((float)$g['amount_won'], 2) ?></td>
            <td class="p-3"><?= e($g['status']) ?></td>
            <td class="p-3"><?= ((int)$g['is_paid'] === 1) ? 'Payé' : '-' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$gains): ?>
        <tr><td colspan="8" class="p-4 text-center text-gray-500">Aucun calcul effectué pour ce tirage.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
