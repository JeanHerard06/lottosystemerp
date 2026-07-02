<?php
require_once "../../config/database.php";
require_once "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'finances.view');
require_once "../../includes/sidebar.php";
require_once "../../includes/topbar.php";

$type = $_GET['type'] ?? '';
$agentId = (int)($_GET['agent_id'] ?? 0);
$where = [];$params=[];
if ($type !== '') { $where[]='t.type=?'; $params[]=$type; }
if ($agentId > 0) { $where[]='t.agent_id=?'; $params[]=$agentId; }
$sql = "SELECT t.*, u.name agent_name FROM agent_transactions t JOIN agents a ON a.id=t.agent_id JOIN users u ON u.id=a.user_id" . ($where ? ' WHERE '.implode(' AND ', $where) : '') . " ORDER BY t.id DESC LIMIT 300";
$stmt=$pdo->prepare($sql);$stmt->execute($params);$transactions=$stmt->fetchAll();
$agents=$pdo->query("SELECT a.id,u.name FROM agents a JOIN users u ON u.id=a.user_id ORDER BY u.name")->fetchAll();
?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Transactions agents</h1><a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Nouvelle transaction</a></div>
<form method="GET" class="bg-white p-4 rounded shadow mb-5 flex gap-3 flex-wrap">
<select name="agent_id" class="border p-2 rounded"><option value="0">Tous agents</option><?php foreach($agents as $a): ?><option value="<?= (int)$a['id'] ?>" <?= $agentId===$a['id']?'selected':'' ?>><?= e($a['name']) ?></option><?php endforeach; ?></select>
<select name="type" class="border p-2 rounded"><option value="">Tous types</option><?php foreach(['depot','retrait','commission','gain','vente'] as $t): ?><option value="<?= $t ?>" <?= $type===$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select>
<button class="bg-black text-white px-4 rounded">Filtrer</button>
</form>
<table class="w-full bg-white rounded shadow text-sm"><thead><tr class="bg-gray-200 text-left"><th class="p-3">Réf.</th><th class="p-3">Agent</th><th class="p-3">Type</th><th class="p-3">Montant</th><th class="p-3">Statut</th><th class="p-3">Description</th><th class="p-3">Date</th><th class="p-3"></th></tr></thead><tbody>
<?php foreach($transactions as $t): ?><tr class="border-b <?= $t['status']==='void'?'opacity-50':'' ?>"><td class="p-3"><?= e($t['reference_no'] ?? '-') ?></td><td class="p-3"><?= e($t['agent_name']) ?></td><td class="p-3"><?= e($t['type']) ?></td><td class="p-3">HTG <?= number_format($t['amount'],2) ?></td><td class="p-3"><?= e($t['status']) ?></td><td class="p-3"><?= e($t['description'] ?? '') ?></td><td class="p-3"><?= e($t['created_at']) ?></td><td class="p-3"><?php if($t['status']==='posted' && has_permission($pdo,'transactions.void')): ?><form method="POST" action="/actions/finances/void.php" onsubmit="return confirm('Annuler cette transaction ?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="text-red-600">Annuler</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require "../../includes/footer.php"; ?>
