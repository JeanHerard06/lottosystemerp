<?php
require "../../../config/database.php";
require "../../../includes/header.php";
require "../../../includes/sidebar.php";
require "../../../includes/topbar.php";
require_once "../../../app/Helpers/permissions.php";
require_permission($pdo, 'payments.view');
$payments=$pdo->query("SELECT p.*, t.name AS tenant_name, i.invoice_no, m.name AS method_name FROM subscription_payments p JOIN tenants t ON t.id=p.tenant_id LEFT JOIN subscription_invoices i ON i.id=p.invoice_id LEFT JOIN payment_methods m ON m.id=p.payment_method_id ORDER BY p.paid_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Paiements SaaS</h1><?php if(has_permission($pdo,'payments.create')): ?><a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Nouveau paiement</a><?php endif; ?></div>
<table class="w-full bg-white rounded shadow"><thead><tr class="bg-gray-200 text-left"><th class="p-3">Tenant</th><th class="p-3">Facture</th><th class="p-3">Méthode</th><th class="p-3">Montant</th><th class="p-3">Référence</th><th class="p-3">Statut</th><th class="p-3">Date</th></tr></thead><tbody>
<?php foreach($payments as $p): ?><tr class="border-b"><td class="p-3"><?= e($p['tenant_name']) ?></td><td class="p-3"><?= e($p['invoice_no']) ?></td><td class="p-3"><?= e($p['method_name']) ?></td><td class="p-3">$<?= number_format((float)$p['amount'],2) ?></td><td class="p-3"><?= e($p['reference_no']) ?></td><td class="p-3"><?= e($p['status']) ?></td><td class="p-3"><?= e($p['paid_at']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require "../../../includes/footer.php"; ?>
