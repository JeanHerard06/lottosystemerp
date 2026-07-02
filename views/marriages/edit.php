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
<?php $stmt=$pdo->prepare('SELECT * FROM marriages WHERE id=?'); $stmt->execute([(int)($_GET['id']??0)]); $row=$stmt->fetch(PDO::FETCH_ASSOC); if(!$row){die('Mariage introuvable');} ?>

<h1 class="text-2xl font-bold mb-5"><?= isset($row) ? 'Modifier mariage' : 'Nouveau mariage' ?></h1>
<form action="<?= isset($row) ? '../../actions/marriages/update.php' : '../../actions/marriages/store.php' ?>" method="POST" class="bg-white p-5 rounded shadow max-w-2xl grid grid-cols-1 md:grid-cols-2 gap-3">
<?= csrf_field() ?><?php if(isset($row)): ?><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><?php endif; ?>
<input name="number1" value="<?= e($row['number1'] ?? '') ?>" placeholder="Numéro 1" class="border p-3 rounded" required><input name="number2" value="<?= e($row['number2'] ?? '') ?>" placeholder="Numéro 2" class="border p-3 rounded" required>
<select name="game_type" class="border p-3 rounded"><option value="mariage">Mariage</option><option value="borlette" <?= (($row['game_type'] ?? '')==='borlette')?'selected':'' ?>>Borlette</option></select>
<select name="lottery_id" class="border p-3 rounded"><option value="">Toutes lotteries</option><?php foreach($lotteries as $l): ?><option value="<?= (int)$l['id'] ?>" <?= ((int)($row['lottery_id'] ?? 0)===(int)$l['id'])?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?></select>
<input type="number" step="0.01" name="payout" value="<?= e($row['payout'] ?? '') ?>" placeholder="Prime/Taux" class="border p-3 rounded" required>
<select name="status" class="border p-3 rounded"><option value="active" <?= (($row['status'] ?? '')==='active')?'selected':'' ?>>Active</option><option value="inactive" <?= (($row['status'] ?? '')==='inactive')?'selected':'' ?>>Inactive</option></select>
<div class="md:col-span-2"><button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button></div>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
