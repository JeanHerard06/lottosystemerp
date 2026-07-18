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
$paidCount = count(array_filter($gagnants, fn($g) => (int)$g['is_paid'] === 1));
$unpaidCount = count($gagnants) - $paidCount;

require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
ui_page_header('Gagnants', 'Suivez les gains calculés, les paiements et les tickets associés.');
?>

<?php if (isset($_GET['paid'])): ?><div class="ui-alert ui-alert-success" data-auto-dismiss="4500">Gain payé avec succès.</div><?php endif; ?>
<?php if (isset($_GET['already_paid'])): ?><div class="ui-alert ui-alert-warning" data-auto-dismiss="4500">Ce gain était déjà payé.</div><?php endif; ?>

<div class="flex flex-wrap gap-2 mb-5" role="tablist" aria-label="Filtre des gains">
    <a href="?filter=won" class="ui-btn <?= $filter==='won'?'ui-btn-primary':'ui-btn-secondary' ?> ui-btn-sm">Tous</a>
    <a href="?filter=unpaid" class="ui-btn <?= $filter==='unpaid'?'ui-btn-primary':'ui-btn-secondary' ?> ui-btn-sm">Non payés</a>
    <a href="?filter=paid" class="ui-btn <?= $filter==='paid'?'ui-btn-primary':'ui-btn-secondary' ?> ui-btn-sm">Payés</a>
</div>

<div class="ui-stat-grid">
    <?php ui_stat_card('Tickets gagnants', (string)count($gagnants), 'blue', 'Selon le filtre actif', null, '🎟'); ?>
    <?php ui_stat_card('Total gains', ui_money((float)$total), 'green', 'Montant cumulé', null, '🏆'); ?>
    <?php ui_stat_card('Non payés', (string)$unpaidCount, $unpaidCount > 0 ? 'amber' : 'slate', $paidCount . ' déjà payés', null, '⌛'); ?>
</div>

<div class="ui-table-panel">
<table class="w-full">
    <thead>
        <tr class="text-left">
            <th class="p-3">Fiche</th><th class="p-3">Agent</th><th class="p-3">Tirage</th><th class="p-3">Numéro</th><th class="p-3">Jeu</th><th class="p-3">Mise</th><th class="p-3">Gain</th><th class="p-3">Paiement</th><th class="p-3">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($gagnants as $g): ?>
        <tr class="border-b">
            <td class="p-3"><a href="fiches/show.php?id=<?= (int)$g['fiche_id'] ?>" class="font-semibold text-blue-700 hover:underline"><?= e($g['fiche_code']) ?></a></td>
            <td class="p-3"><?= e($g['agent_name']) ?></td>
            <td class="p-3"><?= e($g['draw_name']) ?><br><span class="text-xs text-gray-500"><?= e($g['draw_date']) ?></span></td>
            <td class="p-3 font-semibold"><?= e($g['number_played']) ?></td>
            <td class="p-3"><?= e($g['play_type']) ?></td>
            <td class="p-3"><?= ui_money((float)$g['amount_played']) ?></td>
            <td class="p-3 font-bold"><?= ui_money((float)$g['amount_won']) ?></td>
            <td class="p-3"><?= ui_status_badge((int)$g['is_paid'] === 1 ? 'paid' : 'pending', (int)$g['is_paid'] === 1 ? 'Payé' : 'Non payé') ?></td>
            <td class="p-3">
                <?php if ((int)$g['is_paid'] === 0 && has_permission($pdo, 'gains.pay')): ?>
                    <form action="../actions/gains/pay.php" method="POST" data-no-responsive-filter="1">
                        <?= csrf_field() ?>
                        <input type="hidden" name="gain_id" value="<?= (int)$g['id'] ?>">
                        <button class="ui-btn ui-btn-success ui-btn-sm" data-confirm="Confirmer paiement du gain ?">Payer</button>
                    </form>
                <?php else: ?>
                    <span class="text-slate-400">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$gagnants): ?>
        <tr><td colspan="9"><?php ui_empty_state('Aucun gagnant trouvé', 'Aucun gain ne correspond au filtre sélectionné.', '🏆'); ?></td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
