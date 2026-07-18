<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/game_engine.php';
require_permission($pdo, 'controls.manage');
$tenantId = current_tenant_id() ?? 0;
$selectedLotteryId = max(0, (int)($_GET['lottery_id'] ?? 0));
$lotterySql = 'SELECT id, name FROM lotteries WHERE status=1';
$params = [];
if (!is_super_admin()) { $lotterySql .= ' AND tenant_id=?'; $params[] = $tenantId; }
$lotterySql .= ' ORDER BY name';
$ls = $pdo->prepare($lotterySql); $ls->execute($params); $lotteries = $ls->fetchAll(PDO::FETCH_ASSOC);
$allowedLotteryIds = array_map('intval', array_column($lotteries, 'id'));
if ($selectedLotteryId > 0 && !in_array($selectedLotteryId, $allowedLotteryIds, true)) $selectedLotteryId = 0;
$games = game_engine_types($pdo, $tenantId, false);
?>
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
  <div><h1 class="text-2xl font-bold">Configuration des jeux</h1><p class="text-sm text-gray-500">Jeux, validation, moteur gagnant et multiplicateurs propres à chaque tenant.</p></div>
  <form method="GET"><select name="lottery_id" class="border rounded p-2" onchange="this.form.submit()"><option value="0">Règles générales du tenant</option><?php foreach($lotteries as $l): ?><option value="<?= (int)$l['id'] ?>" <?= $selectedLotteryId===(int)$l['id']?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?></select></form>
</div>
<?php if (isset($_GET['saved'])): ?><div class="mb-4 bg-green-100 text-green-800 rounded p-3">Jeu et règles enregistrés.</div><?php endif; ?>
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
<?php foreach ($games as $game):
  $isBorlette = $game['matching_engine'] === 'borlette_position';
  $p1 = game_engine_payout_multiplier($pdo,$game['code'],'position_1',$tenantId,$selectedLotteryId,60);
  $p2 = game_engine_payout_multiplier($pdo,$game['code'],'position_2',$tenantId,$selectedLotteryId,20);
  $p3 = game_engine_payout_multiplier($pdo,$game['code'],'position_3',$tenantId,$selectedLotteryId,10);
  $exact = game_engine_payout_multiplier($pdo,$game['code'],'exact',$tenantId,$selectedLotteryId,0);
?>
<form action="../../actions/games/save.php" method="POST" class="bg-white rounded shadow p-5 space-y-4">
  <?= csrf_field() ?><input type="hidden" name="original_code" value="<?= e($game['code']) ?>"><input type="hidden" name="lottery_id" value="<?= $selectedLotteryId ?>">
  <div class="flex items-center justify-between"><h2 class="font-bold text-lg"><?= e($game['name']) ?></h2><span class="text-xs bg-gray-100 rounded px-2 py-1"><?= e($game['code']) ?></span></div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <label>Nom<input class="border rounded p-2 w-full" name="name" value="<?= e($game['name']) ?>" required></label>
    <label>Code<input class="border rounded p-2 w-full" name="code" value="<?= e($game['code']) ?>" required></label>
    <label>Ordre<input type="number" class="border rounded p-2 w-full" name="display_order" value="<?= (int)$game['display_order'] ?>"></label>
    <label>Moteur<select class="border rounded p-2 w-full" name="matching_engine"><?php foreach (['borlette_position','marriage_any','exact_sequence3','exact_first','any_draw'] as $engine): ?><option value="<?= $engine ?>" <?= $game['matching_engine']===$engine?'selected':'' ?>><?= e($engine) ?></option><?php endforeach; ?></select></label>
    <label>Regex validation<input class="border rounded p-2 w-full" name="validation_pattern" value="<?= e($game['validation_pattern']) ?>"></label>
    <label>Aide de saisie<input class="border rounded p-2 w-full" name="input_hint" value="<?= e($game['input_hint']) ?>"></label>
  </div>
  <?php if ($isBorlette): ?>
  <div class="grid grid-cols-3 gap-3 bg-blue-50 rounded p-3"><label>1er lot<input type="number" step="0.01" min="0" name="payout_position_1" value="<?= e($p1) ?>" class="border rounded p-2 w-full"></label><label>2e lot<input type="number" step="0.01" min="0" name="payout_position_2" value="<?= e($p2) ?>" class="border rounded p-2 w-full"></label><label>3e lot<input type="number" step="0.01" min="0" name="payout_position_3" value="<?= e($p3) ?>" class="border rounded p-2 w-full"></label></div>
  <?php else: ?>
  <label class="block bg-emerald-50 rounded p-3">Multiplicateur exact<input type="number" step="0.01" min="0" name="payout_exact" value="<?= e($exact) ?>" class="border rounded p-2 w-full mt-1"></label>
  <?php endif; ?>
  <label class="flex items-center gap-2"><input type="checkbox" name="enabled" value="1" <?= (int)$game['enabled']===1?'checked':'' ?>> Jeu actif</label>
  <button class="bg-black text-white rounded px-4 py-2">Enregistrer</button>
</form>
<?php endforeach; ?>
<form action="../../actions/games/save.php" method="POST" class="bg-white rounded shadow p-5 space-y-4 border-2 border-dashed">
  <?= csrf_field() ?><input type="hidden" name="lottery_id" value="<?= $selectedLotteryId ?>"><h2 class="font-bold text-lg">Ajouter un jeu tenant</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-3"><label>Nom<input class="border rounded p-2 w-full" name="name" required></label><label>Code<input class="border rounded p-2 w-full" name="code" placeholder="machine" required></label><label>Ordre<input type="number" class="border rounded p-2 w-full" name="display_order" value="100"></label><label>Moteur<select class="border rounded p-2 w-full" name="matching_engine"><option value="exact_first">Exact 1er résultat</option><option value="any_draw">Présent dans les résultats</option></select></label><label>Regex validation<input class="border rounded p-2 w-full" name="validation_pattern" placeholder="^[0-9]{2}$"></label><label>Aide<input class="border rounded p-2 w-full" name="input_hint" placeholder="2 chif, ex: 12"></label></div>
  <label>Multiplicateur exact<input type="number" step="0.01" min="0" name="payout_exact" value="1" class="border rounded p-2 w-full"></label><input type="hidden" name="enabled" value="1"><button class="bg-emerald-600 text-white rounded px-4 py-2">Créer le jeu</button>
</form>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
