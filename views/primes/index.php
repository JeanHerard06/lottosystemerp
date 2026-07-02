<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_permission($pdo, 'controls.manage');
$primes = $pdo->query('SELECT * FROM primes ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-5">Primes / Taux</h1>
<table class="w-full bg-white rounded shadow text-sm">
<thead><tr class="bg-gray-200 text-left"><th class="p-3">Jeu</th><th class="p-3">Taux</th><th class="p-3">Statut</th><th class="p-3">Action</th></tr></thead>
<tbody>
<?php foreach($primes as $p): ?>
<tr class="border-b">
<form action="../../actions/primes/update.php" method="POST">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
<td class="p-3 font-bold"><?= e(ucfirst($p['game_type'])) ?></td>
<td class="p-3"><input type="number" step="0.01" name="payout_rate" value="<?= e($p['payout_rate']) ?>" class="border p-2 rounded w-32"></td>
<td class="p-3"><select name="status" class="border p-2 rounded"><option value="active" <?= $p['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $p['status']==='inactive'?'selected':'' ?>>Inactive</option></select></td>
<td class="p-3"><button class="bg-black text-white px-3 py-2 rounded">Sauver</button></td>
</form>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
