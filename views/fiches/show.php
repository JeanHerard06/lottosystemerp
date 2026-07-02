<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/fiches.php';
require_permission($pdo, 'fiches.view');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT f.*, u.name AS agent_name, l.name AS lottery_name\n    FROM fiches f\n    JOIN agents a ON a.id = f.agent_id\n    JOIN users u ON u.id = a.user_id\n    LEFT JOIN lotteries l ON l.id = f.lottery_id\n    WHERE f.id = ? LIMIT 1");
$stmt->execute([$id]);
$fiche = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$fiche || !can_access_fiche($pdo, $fiche)) {
    http_response_code(404);
    die('Fiche introuvable.');
}
$stmt = $pdo->prepare('SELECT * FROM fiche_details WHERE fiche_id = ? ORDER BY id');
$stmt->execute([$id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT g.*, fd.number_played, fd.play_type, t.draw_name, t.draw_date
    FROM gains g
    JOIN fiche_details fd ON fd.id = g.fiche_detail_id
    JOIN tirages t ON t.id = g.tirage_id
    WHERE fd.fiche_id = ? AND g.status = 'won'
    ORDER BY g.id DESC");
$stmt->execute([$id]);
$gains = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-bold">Fiche <?= e($fiche['fiche_code']) ?></h1>
    <a href="../fiches.php" class="bg-gray-800 text-white px-4 py-2 rounded">Retour</a>
</div>
<?php if (isset($_GET['created'])): ?>
    <div class="bg-green-100 text-green-800 p-4 rounded mb-5">Fiche enregistrée avec succès.</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-5">
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Agent</p><h2 class="font-bold"><?= e($fiche['agent_name']) ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Tirage</p><h2 class="font-bold"><?= e($fiche['lottery_name'] ?? '-') ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Total</p><h2 class="font-bold"><?= number_format((float)$fiche['total_amount'], 2) ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Gain</p><h2 class="font-bold"><?= number_format((float)$fiche['gain_amount'], 2) ?></h2></div>
    <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Statut</p><h2 class="font-bold"><?= e($fiche['status']) ?></h2></div>
</div>

<div class="bg-white rounded shadow overflow-x-auto mb-5">
<table class="w-full">
    <thead><tr class="bg-gray-200 text-left"><th class="p-3">#</th><th class="p-3">Numéro</th><th class="p-3">Jeu</th><th class="p-3">Montant</th></tr></thead>
    <tbody>
    <?php foreach ($details as $i => $d): ?>
        <tr class="border-b"><td class="p-3"><?= $i + 1 ?></td><td class="p-3 font-semibold"><?= e($d['number_played']) ?></td><td class="p-3"><?= e($d['play_type']) ?></td><td class="p-3"><?= number_format((float)$d['amount'], 2) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>


<?php if (!empty($gains)): ?>
<div class="bg-white rounded shadow overflow-x-auto mb-5">
    <div class="p-4 border-b font-bold">Gains liés à cette fiche</div>
    <table class="w-full">
        <thead><tr class="bg-gray-200 text-left"><th class="p-3">Tirage</th><th class="p-3">Numéro</th><th class="p-3">Jeu</th><th class="p-3">Gain</th><th class="p-3">Paiement</th></tr></thead>
        <tbody>
        <?php foreach ($gains as $g): ?>
            <tr class="border-b">
                <td class="p-3"><?= e($g['draw_name']) ?> <span class="text-xs text-gray-500"><?= e($g['draw_date']) ?></span></td>
                <td class="p-3 font-semibold"><?= e($g['number_played']) ?></td>
                <td class="p-3"><?= e($g['play_type']) ?></td>
                <td class="p-3 font-bold"><?= number_format((float)$g['amount_won'], 2) ?></td>
                <td class="p-3"><?= ((int)$g['is_paid'] === 1) ? 'Payé' : 'Non payé' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="flex flex-wrap gap-3">
    <a href="../../actions/print_ticket.php?id=<?= (int)$fiche['id'] ?>" target="_blank" class="bg-green-600 text-white px-4 py-3 rounded">Imprimer / Reprint</a>
    <?php if ($fiche['status'] !== 'cancelled' && has_permission($pdo, 'fiches.cancel')): ?>
        <form action="../../actions/fiches/cancel.php" method="POST" onsubmit="return confirm('Voulez-vous annuler cette fiche ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$fiche['id'] ?>">
            <button class="bg-red-600 text-white px-4 py-3 rounded">Annuler fiche</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
