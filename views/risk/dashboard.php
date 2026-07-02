<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/risk.php';
require_permission($pdo, 'risk.view');
$date = $_GET['date'] ?? date('Y-m-d');
$top = risk_exposure($pdo, $date);
$blockedCount = $pdo->query("SELECT COUNT(*) FROM blocages WHERE status='active' AND (starts_at IS NULL OR starts_at <= NOW()) AND (ends_at IS NULL OR ends_at >= NOW())")->fetchColumn();
$limitsCount = $pdo->query("SELECT COUNT(*) FROM limites WHERE status='active'")->fetchColumn();
$marriagesCount = $pdo->query("SELECT COUNT(*) FROM marriages WHERE status='active'")->fetchColumn();
$totalExposure = array_sum(array_map(fn($r)=>(float)$r['total_played'], $top));
$limitAlerts = $pdo->prepare("\nSELECT li.number_value, li.game_type, li.max_amount, li.threshold_percent,\n       COALESCE(SUM(fd.amount),0) AS played\nFROM limites li\nLEFT JOIN fiche_details fd ON fd.number_played = li.number_value\nLEFT JOIN fiches f ON f.id = fd.fiche_id AND DATE(f.created_at)=? AND f.status <> 'cancelled'\nWHERE li.status='active'\nGROUP BY li.id\nHAVING played >= (li.max_amount * li.threshold_percent / 100)\nORDER BY played DESC\nLIMIT 20\n");
$limitAlerts->execute([$date]);
$limitAlerts = $limitAlerts->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between items-center mb-5">
  <h1 class="text-2xl font-bold">Dashboard Risk Management</h1>
  <form method="GET" class="flex gap-2"><input type="date" name="date" value="<?= e($date) ?>" class="border p-2 rounded"><button class="bg-black text-white px-4 rounded">Filtrer</button></form>
</div>
<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Exposition Top 20</p><h2 class="text-3xl font-bold"><?= number_format($totalExposure,2) ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Blocages actifs</p><h2 class="text-3xl font-bold"><?= (int)$blockedCount ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Limites actives</p><h2 class="text-3xl font-bold"><?= (int)$limitsCount ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Mariages actifs</p><h2 class="text-3xl font-bold"><?= (int)$marriagesCount ?></h2></div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
<div class="bg-white rounded shadow p-5"><h2 class="font-bold text-lg mb-4">Top numéros joués</h2><table class="w-full text-sm"><thead><tr class="bg-gray-100 text-left"><th class="p-2">Numéro</th><th class="p-2">Jeu</th><th class="p-2">Lotterie</th><th class="p-2">Montant</th><th class="p-2">Lignes</th></tr></thead><tbody><?php foreach($top as $r): ?><tr class="border-b"><td class="p-2 font-bold"><?= e($r['number_played']) ?></td><td class="p-2"><?= e($r['play_type']) ?></td><td class="p-2"><?= e($r['lottery_name']) ?></td><td class="p-2"><?= number_format((float)$r['total_played'],2) ?></td><td class="p-2"><?= (int)$r['lines_count'] ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="bg-white rounded shadow p-5"><h2 class="font-bold text-lg mb-4">Alertes limites</h2><table class="w-full text-sm"><thead><tr class="bg-gray-100 text-left"><th class="p-2">Numéro</th><th class="p-2">Jeu</th><th class="p-2">Joué</th><th class="p-2">Limite</th><th class="p-2">%</th></tr></thead><tbody><?php foreach($limitAlerts as $r): $pct = ((float)$r['max_amount']>0) ? ((float)$r['played']/(float)$r['max_amount']*100) : 0; ?><tr class="border-b"><td class="p-2 font-bold"><?= e($r['number_value']) ?></td><td class="p-2"><?= e($r['game_type'] ?: 'Tous') ?></td><td class="p-2"><?= number_format((float)$r['played'],2) ?></td><td class="p-2"><?= number_format((float)$r['max_amount'],2) ?></td><td class="p-2"><?= number_format($pct,2) ?>%</td></tr><?php endforeach; ?></tbody></table></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
