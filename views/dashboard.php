<?php
require "../config/database.php";
require "../includes/header.php";
require_once "../app/Helpers/permissions.php";
require_once "../app/Helpers/tenant.php";
require_permission($pdo, 'dashboard.view');
require "../includes/sidebar.php";
require "../includes/topbar.php";


require_once "../app/Core/Autoload.php";

$dashboard = (new DashboardService($pdo))->build();
$role = $dashboard['role'];
$scopeLabel = $dashboard['scopeLabel'];
$totals = $dashboard['totals'];

$totalVentes = (float)$totals['ventes'];
$totalFiches = (int)$totals['fiches'];
$totalGains = (float)$totals['gains'];
$balance = (float)$totals['balance'];
$totalAgents = (int)$totals['agents'];
$agentsActifs = (int)$totals['agents_actifs'];
$usersCount = (int)$totals['users'];
$tenantsCount = (int)$totals['tenants'];
$tenantActive = (int)$totals['tenants_active'];
$subscriptionsExpiring = (int)$totals['subscriptions_expiring'];
$myBalance = (float)$totals['my_balance'];
$myCommission = (float)$totals['my_commission'];
$topAgents = $dashboard['topAgents'];
$lastFiches = $dashboard['lastFiches'];
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <p class="text-gray-500"><?= e($scopeLabel) ?></p>
</div>

<?php if(is_super_admin()): ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-black text-white p-6 rounded-xl shadow"><p class="text-gray-300">Tenants total</p><h1 class="text-3xl font-bold"><?= $tenantsCount ?></h1></div>
    <div class="bg-white p-6 rounded-xl shadow"><p class="text-gray-500">Tenants actifs</p><h1 class="text-3xl font-bold"><?= $tenantActive ?></h1></div>
    <div class="bg-white p-6 rounded-xl shadow"><p class="text-gray-500">Abonnements à renouveler</p><h1 class="text-3xl font-bold"><?= $subscriptionsExpiring ?></h1></div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
    <div class="bg-white p-6 rounded-xl shadow"><p class="text-gray-500">Ventes</p><h1 class="text-3xl font-bold">$<?= number_format($totalVentes,2) ?></h1></div>
    <div class="bg-white p-6 rounded-xl shadow"><p class="text-gray-500">Fiches</p><h1 class="text-3xl font-bold"><?= $totalFiches ?></h1></div>
    <div class="bg-white p-6 rounded-xl shadow"><p class="text-gray-500">Gains</p><h1 class="text-3xl font-bold">$<?= number_format($totalGains,2) ?></h1></div>
    <div class="bg-white p-6 rounded-xl shadow"><p class="text-gray-500">Balance</p><h1 class="text-3xl font-bold">$<?= number_format($balance,2) ?></h1></div>
</div>

<?php if($role === 'agent'): ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
    <div class="bg-yellow-50 p-5 rounded-xl shadow"><p class="text-gray-500">Ma balance agent</p><h2 class="text-2xl font-bold">$<?= number_format($myBalance,2) ?></h2></div>
    <div class="bg-yellow-50 p-5 rounded-xl shadow"><p class="text-gray-500">Mes commissions</p><h2 class="text-2xl font-bold">$<?= number_format($myCommission,2) ?></h2></div>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-white p-5 rounded-xl shadow"><p class="text-gray-500">Agents actifs</p><h2 class="text-2xl font-bold"><?= $agentsActifs ?></h2></div>
    <div class="bg-white p-5 rounded-xl shadow"><p class="text-gray-500">Agents total</p><h2 class="text-2xl font-bold"><?= $totalAgents ?></h2></div>
    <div class="bg-white p-5 rounded-xl shadow"><p class="text-gray-500">Utilisateurs</p><h2 class="text-2xl font-bold"><?= $usersCount ?></h2></div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="text-xl font-bold mb-4">Top agents</h2>
        <table class="w-full">
            <thead><tr class="text-left text-gray-500"><th class="p-2">Agent</th><th class="p-2">Fiches</th><th class="p-2">Ventes</th></tr></thead>
            <tbody>
            <?php foreach($topAgents as $a): ?>
                <tr class="border-t"><td class="p-2"><?= e($a['name']) ?></td><td class="p-2"><?= (int)$a['fiches'] ?></td><td class="p-2">$<?= number_format((float)$a['ventes'],2) ?></td></tr>
            <?php endforeach; ?>
            <?php if(!$topAgents): ?><tr><td colspan="3" class="p-4 text-gray-500">Aucune donnée disponible.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="text-xl font-bold mb-4">Dernières fiches</h2>
        <table class="w-full">
            <thead><tr class="text-left text-gray-500"><th class="p-2">Code</th><th class="p-2">Agent</th><th class="p-2">Total</th><th class="p-2">Statut</th></tr></thead>
            <tbody>
            <?php foreach($lastFiches as $f): ?>
                <tr class="border-t"><td class="p-2"><?= e($f['fiche_code']) ?></td><td class="p-2"><?= e($f['agent_name']) ?></td><td class="p-2">$<?= number_format((float)$f['total_amount'],2) ?></td><td class="p-2"><?= e($f['status']) ?></td></tr>
            <?php endforeach; ?>
            <?php if(!$lastFiches): ?><tr><td colspan="4" class="p-4 text-gray-500">Aucune fiche.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
