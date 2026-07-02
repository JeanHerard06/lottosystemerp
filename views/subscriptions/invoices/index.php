<?php
require "../../../config/database.php";
require "../../../includes/header.php";
require "../../../includes/sidebar.php";
require "../../../includes/topbar.php";
require_once "../../../app/Helpers/permissions.php";
require_permission($pdo, 'subscriptions.manage');
$invoices=$pdo->query("SELECT i.*, t.name AS tenant_name FROM subscription_invoices i JOIN tenants t ON t.id=i.tenant_id ORDER BY i.id DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Factures d'abonnement</h1><a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Nouvelle facture</a></div>
<table class="w-full bg-white rounded shadow"><thead><tr class="bg-gray-200 text-left"><th class="p-3">No</th><th class="p-3">Tenant</th><th class="p-3">Période</th><th class="p-3">Total</th><th class="p-3">Payé</th><th class="p-3">Statut</th><th class="p-3">Échéance</th></tr></thead><tbody>
<?php foreach($invoices as $i): ?><tr class="border-b"><td class="p-3 font-semibold"><?= e($i['invoice_no']) ?></td><td class="p-3"><?= e($i['tenant_name']) ?></td><td class="p-3"><?= e($i['period_start']) ?> → <?= e($i['period_end']) ?></td><td class="p-3">$<?= number_format((float)$i['total_amount'],2) ?></td><td class="p-3">$<?= number_format((float)$i['paid_amount'],2) ?></td><td class="p-3"><?= e($i['status']) ?></td><td class="p-3"><?= e($i['due_date']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require "../../../includes/footer.php"; ?>
