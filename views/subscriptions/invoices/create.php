<?php
require "../../../config/database.php";
require "../../../includes/header.php";
require "../../../includes/sidebar.php";
require "../../../includes/topbar.php";
require_once "../../../app/Helpers/permissions.php";
require_permission($pdo, 'subscriptions.manage');
$tenants=$pdo->query("SELECT id,name,plan FROM tenants ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-5">Nouvelle facture</h1>
<form action="../../../actions/subscriptions/invoices/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-2xl">
  <?= csrf_field() ?>
  <select name="tenant_id" class="w-full border p-3 mb-3 rounded" required><option value="">Choisir tenant</option><?php foreach($tenants as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['plan']) ?>)</option><?php endforeach; ?></select>
  <div class="grid grid-cols-2 gap-3"><input type="date" name="period_start" class="border p-3 mb-3 rounded"><input type="date" name="period_end" class="border p-3 mb-3 rounded"></div>
  <div class="grid grid-cols-2 gap-3"><input type="number" step="0.01" name="total_amount" placeholder="Montant total" class="border p-3 mb-3 rounded" required><input type="date" name="due_date" class="border p-3 mb-3 rounded"></div>
  <textarea name="notes" placeholder="Notes" class="w-full border p-3 mb-3 rounded"></textarea>
  <button class="bg-black text-white px-5 py-3 rounded">Créer facture</button>
</form>
<?php require "../../../includes/footer.php"; ?>
