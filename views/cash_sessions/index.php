<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/cash_sessions.php';

require_permission($pdo, 'cash_sessions.manage');
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';

[$scopeSql, $scopeParams] = session_visible_clause($pdo, 'cs', 'a', 'WHERE');
$status = $_GET['status'] ?? '';
$params = $scopeParams;
$where = $scopeSql;
if ($status !== '' && in_array($status, ['open','closed','approved','rejected'], true)) {
    $where .= ($where ? ' AND ' : ' WHERE ') . ' cs.status = ? ';
    $params[] = $status;
}
$sql = "SELECT cs.*, u.name AS agent_name, ag.name AS agency_name, t.name AS tenant_name
        FROM cash_sessions cs
        JOIN agents a ON a.id=cs.agent_id
        JOIN users u ON u.id=a.user_id
        LEFT JOIN agencies ag ON ag.id=cs.agency_id
        LEFT JOIN tenants t ON t.id=cs.tenant_id
        $where
        ORDER BY cs.id DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$openCount = count(array_filter($sessions, fn($session) => $session['status'] === 'open'));
$closedCount = count(array_filter($sessions, fn($session) => in_array($session['status'], ['closed','approved','rejected'], true)));
$totalDifference = array_sum(array_map(fn($session) => (float)($session['difference_amount'] ?? 0), $sessions));

ui_page_header('Sessions de caisse', 'Contrôlez les ouvertures, clôtures, écarts et validations.', [
    ['label' => 'Ouvrir session', 'href' => '/views/cash_sessions/open.php', 'class' => 'ui-btn ui-btn-warning', 'icon' => '+'],
]);
?>
<div class="ui-stat-grid">
    <?php ui_stat_card('Sessions visibles', (string)count($sessions), 'blue', 'Selon votre périmètre', null, '💼'); ?>
    <?php ui_stat_card('Ouvertes', (string)$openCount, $openCount > 0 ? 'green' : 'slate', 'À surveiller', null, '●'); ?>
    <?php ui_stat_card('Écart cumulé', ui_money((float)$totalDifference), abs($totalDifference) > 0.009 ? 'amber' : 'slate', $closedCount . ' sessions clôturées', null, '±'); ?>
</div>

<div class="ui-filter-bar">
<form class="ui-filter-form" method="get" data-no-responsive-filter="1">
  <label class="min-w-[12rem] flex-1">
    <span class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Statut</span>
    <select name="status" class="form-control">
      <option value="">Tous statuts</option>
      <?php foreach (['open'=>'Ouverte','closed'=>'Fermée','approved'=>'Approuvée','rejected'=>'Rejetée'] as $k=>$label): ?>
        <option value="<?= e($k) ?>" <?= $status===$k?'selected':'' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button class="ui-btn ui-btn-primary">Filtrer</button>
</form>
</div>

<div class="ui-table-panel">
<table class="w-full">
  <thead><tr class="text-left">
    <th class="p-3">#</th><th class="p-3">Tenant</th><th class="p-3">Agence</th><th class="p-3">Agent</th><th class="p-3">Ouverture</th><th class="p-3">Attendu</th><th class="p-3">Clôture</th><th class="p-3">Diff.</th><th class="p-3">Statut</th><th class="p-3">Action</th>
  </tr></thead>
  <tbody>
  <?php foreach($sessions as $s): ?>
    <tr class="border-b">
      <td class="p-3 font-semibold">#<?= (int)$s['id'] ?></td>
      <td class="p-3"><?= e($s['tenant_name'] ?? '-') ?></td>
      <td class="p-3"><?= e($s['agency_name'] ?? '-') ?></td>
      <td class="p-3"><?= e($s['agent_name'] ?? '-') ?></td>
      <td class="p-3"><?= ui_money((float)$s['opening_amount']) ?></td>
      <td class="p-3"><?= $s['expected_amount'] !== null ? ui_money((float)$s['expected_amount']) : '-' ?></td>
      <td class="p-3"><?= $s['closing_amount'] !== null ? ui_money((float)$s['closing_amount']) : '-' ?></td>
      <td class="p-3 font-semibold <?= ((float)($s['difference_amount'] ?? 0) < 0 ? 'text-red-600' : 'text-green-700') ?>"><?= $s['difference_amount'] !== null ? ui_money((float)$s['difference_amount']) : '-' ?></td>
      <td class="p-3"><?= ui_status_badge((string)$s['status']) ?></td>
      <td class="p-3"><?= ui_action_link('Voir', '/views/cash_sessions/show.php?id=' . (int)$s['id'], 'primary') ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$sessions): ?><tr><td colspan="10"><?php ui_empty_state('Aucune session trouvée', 'Aucune session ne correspond au filtre actif.', '💼'); ?></td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
