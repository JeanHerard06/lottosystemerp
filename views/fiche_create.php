<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/fiches.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';
require_once __DIR__ . '/../app/Helpers/cash_sessions.php';
require_once __DIR__ . '/../app/Helpers/lotteries.php';
require_once __DIR__ . '/../app/Helpers/game_engine.php';
require_permission($pdo, 'fiches.create');
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';

$agent = current_agent($pdo);
$openSession = $agent ? open_cash_session($pdo, (int)$agent['id']) : null;
$tenantId = tenant_value();
if (in_array(current_user_role(), ['admin','super_admin'], true) || !$tenantId) {
    $lotteries = $pdo->query("SELECT id, name, draw_time, close_before_minutes, sales_status FROM lotteries WHERE status = 1 AND sales_status = 'open' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT id, name, draw_time, close_before_minutes, sales_status FROM lotteries WHERE status = 1 AND sales_status = 'open' AND (tenant_id = ? OR tenant_id IS NULL) ORDER BY name");
    $stmt->execute([$tenantId]);
    $lotteries = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$gameTypes = game_engine_types($pdo, $tenantId, true);
$gameOptionsHtml = '';
foreach ($gameTypes as $game) {
    $gameOptionsHtml .= '<option value="' . e($game['code']) . '">' . e($game['name']) . '</option>';
}
?>
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-bold">Nouvelle Fiche</h1>
    <a href="fiches.php" class="text-sm bg-gray-800 text-white px-4 py-2 rounded">Retour</a>
</div>

<?php if (!$agent): ?>
    <div class="bg-red-100 text-red-800 p-4 rounded shadow">Votre utilisateur n'est pas lié à un compte agent. Créez un agent avant de vendre.</div>
<?php elseif (!$openSession): ?>
    <div class="bg-yellow-100 text-yellow-900 p-4 rounded shadow mb-4">Aucune session de caisse ouverte. Vous devez ouvrir une session avant de vendre.</div>
    <a href="/views/cash_sessions/open.php" class="bg-black text-white px-4 py-3 rounded">Ouvrir une session</a>
<?php else: ?>
<form action="../actions/fiche_store.php" method="POST" class="bg-white p-5 rounded shadow max-w-4xl" id="ficheForm">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Agent</label>
            <div class="border p-3 rounded bg-gray-100"><?= e($_SESSION['name'] ?? 'Agent') ?></div>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Tirage / Lotterie</label>
            <select name="lottery_id" class="w-full border p-3 rounded">
                <option value="">Aucun</option>
                <?php foreach ($lotteries as $lottery): ?>
                    <option value="<?= (int)$lottery['id'] ?>"><?= e($lottery['name']) ?><?= !empty($lottery['draw_time']) ? ' — Tirage ' . e(substr($lottery['draw_time'],0,5)) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="playsTable">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-3">Numéro</th>
                    <th class="p-3">Jeu</th>
                    <th class="p-3">Montant</th>
                    <th class="p-3">Action</th>
                </tr>
            </thead>
            <tbody id="plays">
                <tr>
                    <td class="p-2"><input name="numbers[]" placeholder="Ex: 12 ou 12-45" class="number-input w-full border p-3 rounded" required></td>
                    <td class="p-2"><select name="types[]" class="w-full border p-3 rounded"><?= $gameOptionsHtml ?></select></td>
                    <td class="p-2"><input type="number" step="0.01" min="1" name="amounts[]" placeholder="0.00" class="amount-input w-full border p-3 rounded" required></td>
                    <td class="p-2"><button type="button" onclick="removeLine(this)" class="bg-red-600 text-white px-3 py-2 rounded">X</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-3 mt-5">
        <button type="button" onclick="addPlay()" class="bg-yellow-500 text-white px-4 py-3 rounded">+ Ajouter ligne</button>
        <div class="text-xl font-bold">Total: <span id="totalAmount">0.00</span></div>
        <button class="bg-black text-white px-6 py-3 rounded">Enregistrer Fiche</button>
    </div>
</form>

<script>
function lineHtml(){
  return `<tr><td class="p-2"><input name="numbers[]" placeholder="Ex: 12 ou 12-45" class="number-input w-full border p-3 rounded" required></td><td class="p-2"><select name="types[]" class="w-full border p-3 rounded"><?= $gameOptionsHtml ?></select></td><td class="p-2"><input type="number" step="0.01" min="1" name="amounts[]" placeholder="0.00" class="amount-input w-full border p-3 rounded" required></td><td class="p-2"><button type="button" onclick="removeLine(this)" class="bg-red-600 text-white px-3 py-2 rounded">X</button></td></tr>`;
}
function addPlay(){ document.getElementById('plays').insertAdjacentHTML('beforeend', lineHtml()); updateTotal(); }
function removeLine(btn){ if(document.querySelectorAll('#plays tr').length > 1){ btn.closest('tr').remove(); updateTotal(); } }
function updateTotal(){ let total=0; document.querySelectorAll('.amount-input').forEach(i => total += parseFloat(i.value || 0)); document.getElementById('totalAmount').innerText = total.toFixed(2); }
document.addEventListener('input', function(e){ if(e.target.classList.contains('amount-input')) updateTotal(); });
</script>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
