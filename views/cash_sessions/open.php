<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/fiches.php';
require_once __DIR__ . '/../../app/Helpers/cash_sessions.php';

require_permission($pdo, 'cash_sessions.manage');
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';

$agents = [];
$currentAgent = current_agent($pdo);
if (current_user_role() === 'agent') {
    if ($currentAgent) { $agents = [$currentAgent + ['agent_name' => $_SESSION['name'] ?? 'Agent']]; }
} else {
    $where = [];
    $params = [];
    if (!is_super_admin()) { $where[]='a.tenant_id=?'; $params[]=current_tenant_id(); }
    $agencyId = scoped_agency_id($pdo);
    if ($agencyId) { $where[]='a.agency_id=?'; $params[]=$agencyId; }
    $sql = 'SELECT a.*, u.name AS agent_name FROM agents a JOIN users u ON u.id=a.user_id';
    if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
    $sql .= ' ORDER BY u.name';
    $stmt=$pdo->prepare($sql); $stmt->execute($params); $agents=$stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Ouvrir une session de caisse</h1><a href="/views/cash_sessions/index.php" class="bg-gray-800 text-white px-4 py-2 rounded">Retour</a></div>
<form action="/actions/cash_sessions/open.php" method="post" class="bg-white p-5 rounded shadow max-w-xl">
  <?= csrf_field() ?>
  <label class="block text-sm text-gray-600 mb-1">Agent</label>
  <select name="agent_id" class="w-full border p-3 rounded mb-3" <?= current_user_role()==='agent' ? 'readonly' : '' ?> required>
    <?php foreach($agents as $a): ?>
      <option value="<?= (int)$a['id'] ?>"><?= e($a['agent_name'] ?? $a['name'] ?? ('Agent #' . $a['id'])) ?></option>
    <?php endforeach; ?>
  </select>
  <label class="block text-sm text-gray-600 mb-1">Montant d'ouverture</label>
  <input type="number" step="0.01" min="0" name="opening_amount" class="w-full border p-3 rounded mb-3" required>
  <label class="block text-sm text-gray-600 mb-1">Notes</label>
  <textarea name="notes" class="w-full border p-3 rounded mb-3"></textarea>
  <button class="bg-black text-white px-5 py-3 rounded">Ouvrir session</button>
</form>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
