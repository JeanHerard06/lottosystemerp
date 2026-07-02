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
?>
<div class="flex justify-between items-center mb-5">
  <h1 class="text-2xl font-bold">Sessions de caisse</h1>
  <a href="/views/cash_sessions/open.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Ouvrir session</a>
</div>

<form class="bg-white p-4 rounded shadow mb-5 flex gap-3" method="get">
  <select name="status" class="border p-3 rounded">
    <option value="">Tous statuts</option>
    <?php foreach (['open'=>'Ouverte','closed'=>'Fermée','approved'=>'Approuvée','rejected'=>'Rejetée'] as $k=>$label): ?>
      <option value="<?= e($k) ?>" <?= $status===$k?'selected':'' ?>><?= e($label) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="bg-black text-white px-5 rounded">Filtrer</button>
</form>

<table class="w-full bg-white rounded shadow">
  <thead><tr class="bg-gray-200 text-left">
    <th class="p-3">#</th><th class="p-3">Tenant</th><th class="p-3">Agence</th><th class="p-3">Agent</th><th class="p-3">Ouverture</th><th class="p-3">Attendu</th><th class="p-3">Clôture</th><th class="p-3">Diff.</th><th class="p-3">Statut</th><th class="p-3">Action</th>
  </tr></thead>
  <tbody>
  <?php foreach($sessions as $s): ?>
    <tr class="border-b">
      <td class="p-3">#<?= (int)$s['id'] ?></td>
      <td class="p-3"><?= e($s['tenant_name'] ?? '-') ?></td>
      <td class="p-3"><?= e($s['agency_name'] ?? '-') ?></td>
      <td class="p-3"><?= e($s['agent_name'] ?? '-') ?></td>
      <td class="p-3"><?= number_format((float)$s['opening_amount'],2) ?></td>
      <td class="p-3"><?= $s['expected_amount'] !== null ? number_format((float)$s['expected_amount'],2) : '-' ?></td>
      <td class="p-3"><?= $s['closing_amount'] !== null ? number_format((float)$s['closing_amount'],2) : '-' ?></td>
      <td class="p-3 <?= ((float)($s['difference_amount'] ?? 0) < 0 ? 'text-red-600' : 'text-green-700') ?>"><?= $s['difference_amount'] !== null ? number_format((float)$s['difference_amount'],2) : '-' ?></td>
      <td class="p-3"><?= e($s['status']) ?></td>
      <td class="p-3"><a href="/views/cash_sessions/show.php?id=<?= (int)$s['id'] ?>" class="text-blue-600">Voir</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
