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
$role = (string)$dashboard['role'];
$scopeLabel = (string)$dashboard['scopeLabel'];
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
$agentMetrics = is_array($totals['agent_metrics'] ?? null) ? $totals['agent_metrics'] : null;
$topAgents = $dashboard['topAgents'];
$lastFiches = $dashboard['lastFiches'];

require_once __DIR__ . '/components/dashboard_components.php';
?>

<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <p class="text-gray-500"><?= e($scopeLabel) ?></p>
    </div>
    <?php if ($role === 'agent' && $agentMetrics): ?>
        <p class="text-xs text-gray-500">
            Fuseau: <?= e((string)($agentMetrics['diagnostics']['timezone'] ?? '')) ?>
        </p>
    <?php endif; ?>
</div>

<?php if ($role === 'agent' && $agentMetrics): ?>
    <?php
    $cashOnHand = (float)$agentMetrics['cash_on_hand'];
    $amountToRemit = (float)$agentMetrics['amount_to_remit'];
    $gainsPaid = (float)$agentMetrics['today_gains_paid'];
    $notifications = (int)$agentMetrics['unread_notifications'];
    $cashExpected = $agentMetrics['cash_expected'];
    $cashSessionId = $agentMetrics['cash_session_id'];
    ?>

    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
        Dashboard Web Agent aliyen ak menm motè finansye Mobile Agent la: menm tenant, menm ajan, menm lè ak menm règ komisyon.
    </div>

    <section class="dashboard-kpi-grid grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        <?php dashboard_kpi_card('Fiches aujourd’hui', (string)$totalFiches, 'blue', '/views/fiches.php'); ?>
        <?php dashboard_kpi_card('Ventes aujourd’hui', dashboard_money($totalVentes), 'green', '/views/fiches.php'); ?>
        <?php dashboard_kpi_card('Gains', dashboard_money($totalGains), 'purple', '/views/gagnants.php'); ?>
        <?php dashboard_kpi_card('Gains payés', dashboard_money($gainsPaid), 'red', '/views/gagnants.php'); ?>
        <?php dashboard_kpi_card('Commission acquise', dashboard_money($myCommission), 'yellow', '/views/commissions/index.php'); ?>
        <?php dashboard_kpi_card('Encaisse attendue', dashboard_money($cashOnHand), 'blue', $cashSessionId ? '/views/cash_sessions/show.php?id=' . (int)$cashSessionId : '/views/cash_sessions/index.php'); ?>
        <?php dashboard_kpi_card('À remettre', dashboard_money($amountToRemit), 'slate', '/views/cash_sessions/index.php', 'Ventes + dépôts − gains − retraits − commission'); ?>
        <?php dashboard_kpi_card('Notifications non lues', (string)$notifications, 'white', '/views/notifications/index.php'); ?>
    </section>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-2 mb-6">
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-bold">Session de caisse</h2>
                <?php if ($cashSessionId): ?>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Ouverte</span>
                <?php else: ?>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Fermée</span>
                <?php endif; ?>
            </div>
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
                <div class="rounded-xl bg-gray-50 p-3"><dt class="text-gray-500">Ouverture</dt><dd class="font-bold"><?= dashboard_money((float)$agentMetrics['opening_cash']) ?></dd></div>
                <div class="rounded-xl bg-gray-50 p-3"><dt class="text-gray-500">Encaisse session</dt><dd class="font-bold"><?= $cashExpected === null ? '—' : dashboard_money((float)$cashExpected) ?></dd></div>
                <div class="rounded-xl bg-gray-50 p-3"><dt class="text-gray-500">Commission</dt><dd class="font-bold"><?= dashboard_money((float)$agentMetrics['commission_earned']) ?></dd></div>
                <div class="rounded-xl bg-gray-50 p-3"><dt class="text-gray-500">À remettre</dt><dd class="font-bold"><?= dashboard_money($amountToRemit) ?></dd></div>
            </dl>
            <a href="/views/cash_sessions/index.php" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-black px-4 py-2 text-sm font-semibold text-white">Voir la caisse</a>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold mb-4">Définition des montants</h2>
            <div class="space-y-3 text-sm text-gray-700">
                <p><strong>Commission acquise:</strong> calculée selon les règles de jeu et le taux de l’agent.</p>
                <p><strong>Encaisse attendue:</strong> ouverture + ventes + dépôts − gains payés − retraits.</p>
                <p><strong>À remettre:</strong> ventes + dépôts − gains payés − retraits − commission.</p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm border border-gray-100">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold">Mes dernières fiches</h2>
            <a href="/views/fiches.php" class="text-sm font-semibold text-blue-700">Voir tout</a>
        </div>
        <table class="w-full responsive-table">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="p-2">Code</th>
                    <th class="p-2">Total</th>
                    <th class="p-2">Statut</th>
                    <th class="p-2">Agent</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lastFiches as $f): ?>
                <tr class="border-t">
                    <td class="p-2" data-label="Code"><?= e($f['fiche_code']) ?></td>
                    <td class="p-2" data-label="Total"><?= dashboard_money((float)$f['total_amount']) ?></td>
                    <td class="p-2" data-label="Statut"><?= e($f['status']) ?></td>
                    <td class="p-2" data-label="Agent"><?= e($f['agent_name']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$lastFiches): ?><tr><td colspan="4" class="p-4 text-gray-500">Aucune fiche.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>

<?php else: ?>

    <?php if (is_super_admin()): ?>
    <div class="dashboard-kpi-grid grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <?php dashboard_kpi_card('Tenants total', (string)$tenantsCount, 'slate'); ?>
        <?php dashboard_kpi_card('Tenants actifs', (string)$tenantActive, 'white'); ?>
        <?php dashboard_kpi_card('Abonnements à renouveler', (string)$subscriptionsExpiring, 'yellow'); ?>
    </div>
    <?php endif; ?>

    <div class="dashboard-kpi-grid grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
        <?php dashboard_kpi_card('Ventes', dashboard_money($totalVentes), 'green'); ?>
        <?php dashboard_kpi_card('Fiches', (string)$totalFiches, 'blue'); ?>
        <?php dashboard_kpi_card('Gains', dashboard_money($totalGains), 'purple'); ?>
        <?php dashboard_kpi_card('Balance', dashboard_money($balance), 'slate'); ?>
    </div>

    <div class="dashboard-kpi-grid grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <?php dashboard_kpi_card('Agents actifs', (string)$agentsActifs, 'green'); ?>
        <?php dashboard_kpi_card('Agents total', (string)$totalAgents, 'white'); ?>
        <?php dashboard_kpi_card('Utilisateurs', (string)$usersCount, 'white'); ?>
    </div>

    <div class="dashboard-section-grid grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-4">Top agents</h2>
            <table class="w-full responsive-table">
                <thead><tr class="text-left text-gray-500"><th class="p-2">Agent</th><th class="p-2">Fiches</th><th class="p-2">Ventes</th></tr></thead>
                <tbody>
                <?php foreach ($topAgents as $a): ?>
                    <tr class="border-t"><td class="p-2" data-label="Agent"><?= e($a['name']) ?></td><td class="p-2" data-label="Fiches"><?= (int)$a['fiches'] ?></td><td class="p-2" data-label="Ventes"><?= dashboard_money((float)$a['ventes']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$topAgents): ?><tr><td colspan="3" class="p-4 text-gray-500">Aucune donnée disponible.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-xl font-bold mb-4">Dernières fiches</h2>
            <table class="w-full responsive-table">
                <thead><tr class="text-left text-gray-500"><th class="p-2">Code</th><th class="p-2">Agent</th><th class="p-2">Total</th><th class="p-2">Statut</th></tr></thead>
                <tbody>
                <?php foreach ($lastFiches as $f): ?>
                    <tr class="border-t"><td class="p-2" data-label="Code"><?= e($f['fiche_code']) ?></td><td class="p-2" data-label="Agent"><?= e($f['agent_name']) ?></td><td class="p-2" data-label="Total"><?= dashboard_money((float)$f['total_amount']) ?></td><td class="p-2" data-label="Statut"><?= e($f['status']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$lastFiches): ?><tr><td colspan="4" class="p-4 text-gray-500">Aucune fiche.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php require "../includes/footer.php"; ?>
