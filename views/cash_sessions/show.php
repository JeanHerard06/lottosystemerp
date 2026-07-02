<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/cash_sessions.php';

require_permission($pdo, 'cash_sessions.manage');
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT cs.*, u.name AS agent_name, ag.name AS agency_name, t.name AS tenant_name
    FROM cash_sessions cs
    JOIN agents a ON a.id=cs.agent_id
    JOIN users u ON u.id=a.user_id
    LEFT JOIN agencies ag ON ag.id=cs.agency_id
    LEFT JOIN tenants t ON t.id=cs.tenant_id
    WHERE cs.id=? LIMIT 1');
$stmt->execute([$id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($session ?: null, 'session caisse');
if (current_user_role() === 'agent') {
    $agent = current_agent_record($pdo);
    if (!$agent || (int)$session['agent_id'] !== (int)$agent['id']) { http_response_code(403); die('Accès refusé.'); }
}
$totals = cash_session_totals($pdo, $id);
$expectedLive = cash_expected_amount((float)$session['opening_amount'], $totals);
$stmt = $pdo->prepare('SELECT * FROM agent_transactions WHERE cash_session_id=? ORDER BY id DESC LIMIT 100');
$stmt->execute([$id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between items-center mb-5"><h1 class="text-2xl font-bold">Session caisse #<?= (int)$session['id'] ?></h1><a href="/views/cash_sessions/index.php" class="bg-gray-800 text-white px-4 py-2 rounded">Retour</a></div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Agent</p><h2 class="font-bold"><?= e($session['agent_name']) ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Agence</p><h2 class="font-bold"><?= e($session['agency_name'] ?? '-') ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Statut</p><h2 class="font-bold"><?= e($session['status']) ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Ouverte le</p><h2 class="font-bold"><?= e($session['opened_at']) ?></h2></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-5">
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Ouverture</p><h2 class="text-xl font-bold"><?= number_format((float)$session['opening_amount'],2) ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Ventes</p><h2 class="text-xl font-bold"><?= number_format($totals['sales'],2) ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Gains payés</p><h2 class="text-xl font-bold"><?= number_format($totals['paid_gains'],2) ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Dépôts</p><h2 class="text-xl font-bold"><?= number_format($totals['deposits'],2) ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Retraits</p><h2 class="text-xl font-bold"><?= number_format($totals['withdrawals'],2) ?></h2></div>
  <div class="bg-white p-4 rounded shadow"><p class="text-gray-500">Attendu</p><h2 class="text-xl font-bold"><?= number_format($session['expected_amount'] !== null ? (float)$session['expected_amount'] : $expectedLive,2) ?></h2></div>
</div>

<?php if ($session['status'] === 'open'): ?>
<form action="/actions/cash_sessions/close.php" method="post" class="bg-white p-5 rounded shadow max-w-xl mb-5">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$session['id'] ?>">
  <h2 class="text-lg font-bold mb-3">Fermer la session</h2>
  <input type="number" step="0.01" min="0" name="closing_amount" placeholder="Montant réel en caisse" class="w-full border p-3 rounded mb-3" required>
  <textarea name="notes" placeholder="Notes de fermeture" class="w-full border p-3 rounded mb-3"></textarea>
  <button class="bg-black text-white px-5 py-3 rounded">Fermer session</button>
</form>
<?php elseif ($session['status'] === 'closed' && has_permission($pdo, 'cash_sessions.approve')): ?>
<div class="bg-white p-5 rounded shadow mb-5 flex gap-3">
  <form action="/actions/cash_sessions/approve.php" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$session['id'] ?>"><input type="hidden" name="decision" value="approved"><button class="bg-green-700 text-white px-5 py-3 rounded">Approuver</button></form>
  <form action="/actions/cash_sessions/approve.php" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$session['id'] ?>"><input type="hidden" name="decision" value="rejected"><button class="bg-red-700 text-white px-5 py-3 rounded">Rejeter</button></form>
</div>
<?php endif; ?>

<h2 class="text-xl font-bold mb-3">Transactions liées</h2>
<table class="w-full bg-white rounded shadow"><thead><tr class="bg-gray-200 text-left"><th class="p-3">Réf.</th><th class="p-3">Type</th><th class="p-3">Montant</th><th class="p-3">Description</th><th class="p-3">Date</th></tr></thead><tbody>
<?php foreach($transactions as $t): ?><tr class="border-b"><td class="p-3"><?= e($t['reference_no'] ?? '-') ?></td><td class="p-3"><?= e($t['type']) ?></td><td class="p-3"><?= number_format((float)$t['amount'],2) ?></td><td class="p-3"><?= e($t['description'] ?? '') ?></td><td class="p-3"><?= e($t['created_at']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
