<?php
require_once "../../config/database.php";
require_once "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'finances.view');
require_once "../../includes/sidebar.php";
require_once "../../includes/topbar.php";
$agents=$pdo->query("SELECT a.*,u.name,u.username,ag.name agency_name,
COALESCE(SUM(CASE WHEN t.type='depot' AND t.status='posted' THEN t.amount ELSE 0 END),0) depots,
COALESCE(SUM(CASE WHEN t.type='retrait' AND t.status='posted' THEN t.amount ELSE 0 END),0) retraits,
COALESCE(SUM(CASE WHEN t.type='commission' AND t.status='posted' THEN t.amount ELSE 0 END),0) commissions,
COALESCE(SUM(CASE WHEN t.type='gain' AND t.status='posted' THEN t.amount ELSE 0 END),0) gains
FROM agents a JOIN users u ON u.id=a.user_id LEFT JOIN agencies ag ON ag.id=a.agency_id LEFT JOIN agent_transactions t ON t.agent_id=a.id GROUP BY a.id ORDER BY u.name")->fetchAll();
?>
<h1 class="text-2xl font-bold mb-5">Balances agents</h1>
<table class="w-full bg-white rounded shadow text-sm"><thead><tr class="bg-gray-200 text-left"><th class="p-3">Agent</th><th class="p-3">Agence</th><th class="p-3">Dépôts</th><th class="p-3">Retraits</th><th class="p-3">Commissions</th><th class="p-3">Gains</th><th class="p-3">Balance</th><th class="p-3"></th></tr></thead><tbody><?php foreach($agents as $a): ?><tr class="border-b"><td class="p-3"><?= e((is_super_admin() ? ('#'.($a['tenant_id'] ?? '-') . ' - ') : '') . $a['name']) ?><br><span class="text-gray-500"><?= e($a['username']) ?></span></td><td class="p-3"><?= e($a['agency_name'] ?? '-') ?></td><td class="p-3">HTG <?= number_format($a['depots'],2) ?></td><td class="p-3">HTG <?= number_format($a['retraits'],2) ?></td><td class="p-3">HTG <?= number_format($a['commissions'],2) ?></td><td class="p-3">HTG <?= number_format($a['gains'],2) ?></td><td class="p-3 font-bold">HTG <?= number_format($a['balance'],2) ?></td><td class="p-3"><a class="text-blue-600" href="transactions.php?agent_id=<?= (int)$a['id'] ?>">Ledger</a></td></tr><?php endforeach; ?></tbody></table>
<?php require "../../includes/footer.php"; ?>
