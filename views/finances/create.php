<?php
require_once "../../config/database.php";
require_once "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'finances.manage');
require_once "../../includes/sidebar.php";
require_once "../../includes/topbar.php";
[$tenantSql, $tenantParams] = tenant_scope_clause('a', 'WHERE');
$stmt = $pdo->prepare("SELECT a.id, a.balance, u.name, ag.name agency_name FROM agents a JOIN users u ON u.id=a.user_id AND u.tenant_id=a.tenant_id LEFT JOIN agencies ag ON ag.id=a.agency_id AND ag.tenant_id=a.tenant_id {$tenantSql} ORDER BY u.name");
$stmt->execute($tenantParams);
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-5">Nouvelle transaction</h1>
<form action="/actions/finances/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
  <?= csrf_field() ?>
  <label class="block mb-2 font-semibold">Agent</label>
  <select name="agent_id" class="w-full border p-3 mb-3 rounded" required>
    <option value="">Choisir agent</option>
    <?php foreach($agents as $a): ?>
      <option value="<?= (int)$a['id'] ?>"><?= e((is_super_admin() ? ('#'.($a['tenant_id'] ?? '-') . ' - ') : '') . $a['name']) ?> — <?= e($a['agency_name'] ?? '-') ?> — Balance: HTG <?= number_format($a['balance'],2) ?></option>
    <?php endforeach; ?>
  </select>
  <label class="block mb-2 font-semibold">Type</label>
  <select name="type" class="w-full border p-3 mb-3 rounded" required>
    <option value="depot">Dépôt</option>
    <option value="retrait">Retrait</option>
  </select>
  <input type="number" step="0.01" min="0.01" name="amount" placeholder="Montant" class="w-full border p-3 mb-3 rounded" required>
  <textarea name="description" placeholder="Description" class="w-full border p-3 mb-3 rounded"></textarea>
  <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require "../../includes/footer.php"; ?>
