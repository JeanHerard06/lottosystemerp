<?php
require "../../config/database.php";
require "../../includes/header.php";
require "../../includes/sidebar.php";
require "../../includes/topbar.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'payments.view');
$activeTenants=$pdo->query("SELECT COUNT(*) FROM tenants WHERE status='active'")->fetchColumn();
$expired=$pdo->query("SELECT COUNT(*) FROM tenants WHERE expires_at IS NOT NULL AND expires_at < CURDATE()")->fetchColumn();
$unpaid=$pdo->query("SELECT COALESCE(SUM(total_amount-paid_amount),0) FROM subscription_invoices WHERE status IN ('issued','partial','overdue')")->fetchColumn();
$paidMonth=$pdo->query("SELECT COALESCE(SUM(amount),0) FROM subscription_payments WHERE status='completed' AND DATE_FORMAT(paid_at,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();
?>
<h1 class="text-2xl font-bold mb-5">Abonnements SaaS</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Tenants actifs</p><h2 class="text-3xl font-bold"><?= (int)$activeTenants ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Expirés</p><h2 class="text-3xl font-bold"><?= (int)$expired ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">À recevoir</p><h2 class="text-3xl font-bold">$<?= number_format((float)$unpaid,2) ?></h2></div>
  <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Payé ce mois</p><h2 class="text-3xl font-bold">$<?= number_format((float)$paidMonth,2) ?></h2></div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
  <a href="plans/index.php" class="bg-white p-6 rounded shadow hover:bg-yellow-50">Plans SaaS</a>
  <a href="invoices/index.php" class="bg-white p-6 rounded shadow hover:bg-yellow-50">Factures</a>
  <a href="payments/index.php" class="bg-white p-6 rounded shadow hover:bg-yellow-50">Paiements</a>
</div>
<?php require "../../includes/footer.php"; ?>
