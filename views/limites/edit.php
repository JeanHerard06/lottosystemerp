<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_permission($pdo, 'controls.manage');
[$lotteryTenantSql, $lotteryTenantParams] = tenant_scope_clause('', 'AND');
$stmt = $pdo->prepare('SELECT id, name FROM lotteries WHERE status=1 ' . $lotteryTenantSql . ' ORDER BY name');
$stmt->execute($lotteryTenantParams);
$lotteries = $stmt->fetchAll(PDO::FETCH_ASSOC);
$agencies = visible_agencies($pdo, true);
$gameTypes = ['borlette'=>'Borlette','mariage'=>'Mariage','lotto3'=>'Lotto 3','lotto4'=>'Lotto 4','*'=>'Tous'];
?>
<?php $stmt=$pdo->prepare('SELECT * FROM limites WHERE id=?'); $stmt->execute([(int)($_GET['id']??0)]); $row=$stmt->fetch(PDO::FETCH_ASSOC); if(!$row){die('Limite introuvable');} ?>

<h1 class="text-2xl font-bold mb-5"><?= isset($row) ? 'Modifier limite' : 'Nouvelle limite' ?></h1>
<form action="<?= isset($row) ? '../../actions/limites/update.php' : '../../actions/limites/store.php' ?>" method="POST" class="bg-white p-5 rounded shadow max-w-2xl grid grid-cols-1 md:grid-cols-2 gap-3">
<?= csrf_field() ?><?php if(isset($row)): ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><?php endif; ?>
<input name="number_value" value="<?= e($row['number_value'] ?? '') ?>" placeholder="Numéro ex: 17" class="border p-3 rounded" required>
<select name="game_type" class="border p-3 rounded"><option value="">Tous jeux</option><?php foreach($gameTypes as $k=>$v): ?><option value="<?= e($k) ?>" <?= (($row['game_type'] ?? '')===$k)?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select>
<select name="lottery_id" class="border p-3 rounded"><option value="">Toutes lotteries</option><?php foreach($lotteries as $l): ?><option value="<?= (int)$l['id'] ?>" <?= ((int)($row['lottery_id'] ?? 0)===(int)$l['id'])?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?></select>
<select name="agency_id" class="border p-3 rounded"><option value="">Toutes agences</option><?php foreach($agencies as $a): ?><option value="<?= (int)$a['id'] ?>" <?= ((int)($row['agency_id'] ?? 0)===(int)$a['id'])?'selected':'' ?>><?= e((is_super_admin() ? ('#'.($a['tenant_id'] ?? '-') . ' - ') : '') . $a['name']) ?></option><?php endforeach; ?></select>
<input type="number" step="0.01" name="max_amount" value="<?= e($row['max_amount'] ?? '') ?>" placeholder="Montant limite" class="border p-3 rounded" required>
<input type="number" step="0.01" name="threshold_percent" value="<?= e($row['threshold_percent'] ?? '80') ?>" placeholder="Alerte %" class="border p-3 rounded">
<select name="status" class="border p-3 rounded"><option value="active" <?= (($row['status'] ?? '')==='active')?'selected':'' ?>>Active</option><option value="inactive" <?= (($row['status'] ?? '')==='inactive')?'selected':'' ?>>Inactive</option></select>
<div class="md:col-span-2"><button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button></div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
