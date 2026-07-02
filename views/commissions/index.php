<?php
require_once "../../config/database.php";
require_once "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'commissions.manage');
require_once "../../includes/sidebar.php";
require_once "../../includes/topbar.php";
$agents=$pdo->query("SELECT a.id,u.name FROM agents a JOIN users u ON u.id=a.user_id ORDER BY u.name")->fetchAll();
$agentId=(int)($_GET['agent_id'] ?? ($agents[0]['id'] ?? 0));
$rows=[];
if($agentId>0){$stmt=$pdo->prepare("SELECT game_type, percentage FROM commissions WHERE agent_id=?");$stmt->execute([$agentId]);foreach($stmt->fetchAll() as $r){$rows[$r['game_type']]=$r['percentage'];}}
$defaults=['borlette'=>10,'mariage'=>12,'lotto3'=>15,'lotto4'=>20];
?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Commissions Agents</h1></div>
<form method="GET" class="bg-white p-4 rounded shadow mb-5 max-w-xl"><label class="font-semibold">Agent</label><select name="agent_id" onchange="this.form.submit()" class="w-full border p-3 mt-2 rounded"><?php foreach($agents as $a): ?><option value="<?= (int)$a['id'] ?>" <?= $agentId===(int)$a['id']?'selected':'' ?>><?= e($a['name']) ?></option><?php endforeach; ?></select></form>
<?php if($agentId>0): ?>
<form method="POST" action="/actions/commissions/update.php" class="bg-white p-5 rounded shadow max-w-xl">
<?= csrf_field() ?><input type="hidden" name="agent_id" value="<?= $agentId ?>">
<?php foreach($defaults as $game=>$default): $val=$rows[$game] ?? $default; ?>
<label class="block mb-2 font-semibold"><?= ucfirst($game) ?> (%)</label>
<input type="number" step="0.01" min="0" max="100" name="rates[<?= $game ?>]" value="<?= e((string)$val) ?>" class="w-full border p-3 mb-3 rounded">
<?php endforeach; ?>
<button class="bg-black text-white px-5 py-3 rounded">Enregistrer commissions</button>
</form>
<?php endif; ?>
<?php require "../../includes/footer.php"; ?>
