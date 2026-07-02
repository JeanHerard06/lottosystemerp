<?php
require_once "../../config/database.php";
require_once "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'finances.view');
require_once "../../includes/sidebar.php";
require_once "../../includes/topbar.php";

$stats = $pdo->query("SELECT
  COALESCE(SUM(CASE WHEN type='depot' AND status='posted' THEN amount ELSE 0 END),0) depots,
  COALESCE(SUM(CASE WHEN type='retrait' AND status='posted' THEN amount ELSE 0 END),0) retraits,
  COALESCE(SUM(CASE WHEN type='commission' AND status='posted' THEN amount ELSE 0 END),0) commissions,
  COALESCE(SUM(CASE WHEN type='gain' AND status='posted' THEN amount ELSE 0 END),0) gains
FROM agent_transactions")->fetch();
$balances = $pdo->query("SELECT COALESCE(SUM(balance),0) FROM agents")->fetchColumn();
$recent = $pdo->query("SELECT t.*, u.name agent_name FROM agent_transactions t JOIN agents a ON a.id=t.agent_id JOIN users u ON u.id=a.user_id ORDER BY t.id DESC LIMIT 10")->fetchAll();
?>
<div class="flex justify-between items-center mb-6">
  <h1 class="text-2xl font-bold">Dashboard Finances</h1>
  <a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Dépôt / Retrait</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Dépôts</p><h2 class="text-2xl font-bold">HTG <?= number_format($stats['depots'],2) ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Retraits</p><h2 class="text-2xl font-bold">HTG <?= number_format($stats['retraits'],2) ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Commissions</p><h2 class="text-2xl font-bold">HTG <?= number_format($stats['commissions'],2) ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Gains payés</p><h2 class="text-2xl font-bold">HTG <?= number_format($stats['gains'],2) ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Balance agents</p><h2 class="text-2xl font-bold">HTG <?= number_format($balances,2) ?></h2></div>
</div>
<div class="bg-white rounded shadow overflow-hidden">
<table class="w-full text-sm">
<thead class="bg-gray-200 text-left"><tr><th class="p-3">Réf.</th><th class="p-3">Agent</th><th class="p-3">Type</th><th class="p-3">Montant</th><th class="p-3">Statut</th><th class="p-3">Date</th></tr></thead>
<tbody><?php foreach($recent as $t): ?><tr class="border-b"><td class="p-3"><?= e($t['reference_no'] ?? '-') ?></td><td class="p-3"><?= e($t['agent_name']) ?></td><td class="p-3"><?= e($t['type']) ?></td><td class="p-3">HTG <?= number_format($t['amount'],2) ?></td><td class="p-3"><?= e($t['status']) ?></td><td class="p-3"><?= e($t['created_at']) ?></td></tr><?php endforeach; ?></tbody>
</table>
</div>
<?php require "../../includes/footer.php"; ?>
