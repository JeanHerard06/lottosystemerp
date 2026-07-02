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

<?php [$scopeSql, $scopeParams] = tenant_scope_clause('m', 'WHERE');
$stmt = $pdo->prepare("SELECT m.*, lo.name lottery_name FROM marriages m LEFT JOIN lotteries lo ON lo.id=m.lottery_id AND lo.tenant_id=m.tenant_id {$scopeSql} ORDER BY m.id DESC");
$stmt->execute($scopeParams);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Mariages</h1><a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Nouveau mariage</a></div>
<table class="w-full bg-white rounded shadow text-sm"><thead><tr class="bg-gray-200 text-left"><th class="p-3">Combinaison</th><th class="p-3">Jeu</th><th class="p-3">Lotterie</th><th class="p-3">Prime</th><th class="p-3">Statut</th><th class="p-3">Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr class="border-b"><td class="p-3 font-bold"><?= e($r['number1'].'-'.$r['number2']) ?></td><td class="p-3"><?= e($r['game_type']) ?></td><td class="p-3"><?= e($r['lottery_name'] ?: 'Toutes') ?></td><td class="p-3"><?= number_format((float)$r['payout'],2) ?></td><td class="p-3"><?= e($r['status']) ?></td><td class="p-3 flex gap-2"><a class="text-blue-600" href="edit.php?id=<?= (int)$r['id'] ?>">Modifier</a><form action="../../actions/marriages/delete.php" method="POST" onsubmit="return confirm('Supprimer ce mariage ?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="text-red-600">Supprimer</button></form></td></tr><?php endforeach; ?></tbody></table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
