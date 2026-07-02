<?php
require "../../../config/database.php";
require "../../../includes/header.php";
require "../../../includes/sidebar.php";
require "../../../includes/topbar.php";
require_once "../../../app/Helpers/permissions.php";
require_permission($pdo, 'payments.create');
$invoices=$pdo->query("SELECT i.id,i.invoice_no,i.tenant_id,i.total_amount,i.paid_amount,t.name AS tenant_name FROM subscription_invoices i JOIN tenants t ON t.id=i.tenant_id WHERE i.status NOT IN ('paid','void') ORDER BY i.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$methods=$pdo->query("SELECT id,name FROM payment_methods WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-5">Nouveau paiement</h1>
<form action="../../../actions/subscriptions/payments/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-2xl">
  <?= csrf_field() ?>
  <select name="invoice_id" class="w-full border p-3 mb-3 rounded" required><option value="">Choisir facture</option><?php foreach($invoices as $i): $reste=(float)$i['total_amount']-(float)$i['paid_amount']; ?><option value="<?= (int)$i['id'] ?>"><?= e($i['invoice_no']) ?> - <?= e($i['tenant_name']) ?> - Reste $<?= number_format($reste,2) ?></option><?php endforeach; ?></select>
  <select name="payment_method_id" class="w-full border p-3 mb-3 rounded"><?php foreach($methods as $m): ?><option value="<?= (int)$m['id'] ?>"><?= e($m['name']) ?></option><?php endforeach; ?></select>
  <div class="grid grid-cols-2 gap-3"><input type="number" step="0.01" name="amount" placeholder="Montant" class="border p-3 mb-3 rounded" required><input type="datetime-local" name="paid_at" class="border p-3 mb-3 rounded"></div>
  <input name="reference_no" placeholder="Référence paiement" class="w-full border p-3 mb-3 rounded">
  <textarea name="notes" placeholder="Notes" class="w-full border p-3 mb-3 rounded"></textarea>
  <button class="bg-black text-white px-5 py-3 rounded">Enregistrer paiement</button>
</form>
<?php require "../../../includes/footer.php"; ?>
