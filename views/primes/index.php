<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/gains.php';
require_permission($pdo, 'controls.manage');
$primes = $pdo->query('SELECT * FROM primes ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$settingsTenantId = current_tenant_id() ?? 0;
$selectedLotteryId = max(0, (int)($_GET['lottery_id'] ?? 0));

$lotterySql = 'SELECT id, tenant_id, name FROM lotteries WHERE status = 1';
$lotteryParams = [];
if (!is_super_admin()) {
    $lotterySql .= ' AND tenant_id = ?';
    $lotteryParams[] = $settingsTenantId;
}
$lotterySql .= ' ORDER BY name';
$lotteryStmt = $pdo->prepare($lotterySql);
$lotteryStmt->execute($lotteryParams);
$lotteries = $lotteryStmt->fetchAll(PDO::FETCH_ASSOC);

if ($selectedLotteryId > 0) {
    $allowedLotteryIds = array_map('intval', array_column($lotteries, 'id'));
    if (!in_array($selectedLotteryId, $allowedLotteryIds, true)) {
        $selectedLotteryId = 0;
    }
}

$borlettePayouts = get_borlette_payouts($pdo, $settingsTenantId, 0);
$extendedPayouts = get_extended_game_payouts($pdo, $settingsTenantId, $selectedLotteryId);
?>
<h1 class="text-2xl font-bold mb-5">Primes / Taux</h1>
<div class="bg-white rounded shadow p-5 mb-6">
  <div class="flex items-center justify-between gap-4 mb-4">
    <div>
      <h2 class="text-lg font-bold">Barème Bòlèt Ayiti</h2>
      <p class="text-sm text-gray-500">Multiplicateurs selon la position du numéro gagnant: 1er, 2e ou 3e lot.</p>
    </div>
    <span class="text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-700">60 / 20 / 10</span>
  </div>
  <form action="../../actions/primes/update_borlette_positions.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <?= csrf_field() ?>
    <label class="block">
      <span class="text-sm font-medium">1er lot</span>
      <input type="number" step="0.01" min="0" name="payout_1" value="<?= e($borlettePayouts[1]) ?>" class="mt-1 border p-2 rounded w-full">
    </label>
    <label class="block">
      <span class="text-sm font-medium">2e lot</span>
      <input type="number" step="0.01" min="0" name="payout_2" value="<?= e($borlettePayouts[2]) ?>" class="mt-1 border p-2 rounded w-full">
    </label>
    <label class="block">
      <span class="text-sm font-medium">3e lot</span>
      <input type="number" step="0.01" min="0" name="payout_3" value="<?= e($borlettePayouts[3]) ?>" class="mt-1 border p-2 rounded w-full">
    </label>
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Enregistrer le barème</button>
  </form>
  <div class="mt-4 text-sm text-gray-600">Exemple avec une mise de 50 HTG: 1er lot = 3 000 HTG, 2e lot = 1 000 HTG, 3e lot = 500 HTG.</div>
</div>

<div class="bg-white rounded shadow p-5 mb-6">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
    <div>
      <h2 class="text-lg font-bold">Règles Mariage / Lotto 3 / Lotto 4</h2>
      <p class="text-sm text-gray-500">Chaque tenant peut définir ses propres multiplicateurs. Une règle spécifique à une lottery remplace la règle générale du tenant.</p>
    </div>
    <form method="GET" class="flex items-end gap-2">
      <label class="block">
        <span class="text-xs font-medium text-gray-600">Portée</span>
        <select name="lottery_id" class="mt-1 border p-2 rounded min-w-56" onchange="this.form.submit()">
          <option value="0">Toutes les lotteries (règle tenant)</option>
          <?php foreach ($lotteries as $lottery): ?>
            <option value="<?= (int)$lottery['id'] ?>" <?= $selectedLotteryId === (int)$lottery['id'] ? 'selected' : '' ?>>
              <?= e($lottery['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <noscript><button class="bg-gray-700 text-white px-3 py-2 rounded">Charger</button></noscript>
    </form>
  </div>

  <?php if (isset($_GET['saved_games'])): ?>
    <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-3">Règles de paiement enregistrées.</div>
  <?php endif; ?>

  <form action="../../actions/primes/update_extended_games.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <?= csrf_field() ?>
    <input type="hidden" name="lottery_id" value="<?= $selectedLotteryId ?>">
    <label class="block">
      <span class="text-sm font-medium">Mariage</span>
      <input type="number" step="0.01" min="0" name="payout_mariage" value="<?= e($extendedPayouts['mariage']) ?>" class="mt-1 border p-2 rounded w-full">
      <span class="text-xs text-gray-500">Gain = mise × multiplicateur</span>
    </label>
    <label class="block">
      <span class="text-sm font-medium">Lotto 3</span>
      <input type="number" step="0.01" min="0" name="payout_lotto3" value="<?= e($extendedPayouts['lotto3']) ?>" class="mt-1 border p-2 rounded w-full">
      <span class="text-xs text-gray-500">Gain = mise × multiplicateur</span>
    </label>
    <label class="block">
      <span class="text-sm font-medium">Lotto 4</span>
      <input type="number" step="0.01" min="0" name="payout_lotto4" value="<?= e($extendedPayouts['lotto4']) ?>" class="mt-1 border p-2 rounded w-full">
      <span class="text-xs text-gray-500">Gain = mise × multiplicateur</span>
    </label>
    <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Enregistrer les règles</button>
  </form>

  <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
    <div class="rounded bg-gray-50 p-3"><strong>Mariage:</strong> deux numéros gagnants présents parmi les trois résultats.</div>
    <div class="rounded bg-gray-50 p-3"><strong>Lotto 3:</strong> combinaison exacte selon la règle actuelle du moteur.</div>
    <div class="rounded bg-gray-50 p-3"><strong>Lotto 4:</strong> numéro exact selon la règle actuelle du moteur.</div>
  </div>
</div>

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
