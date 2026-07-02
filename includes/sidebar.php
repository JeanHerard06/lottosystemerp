<?php
require_once __DIR__ . '/../app/Helpers/permissions.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';

if (!function_exists('render_sidebar_nav')) {
    function render_sidebar_nav(PDO $pdo): void
    {
        ?>
        <nav class="space-y-2 text-sm pb-8">
            <?php if (has_permission($pdo, 'dashboard.view')): ?>
                <a href="/views/dashboard.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Tableau de bord</a>
            <?php endif; ?>

            <?php if (is_super_admin()): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">SaaS</p>
                <a href="/views/tenants/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Tenants / Banques</a>
                <a href="/views/tenant_registrations/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Demandes tenant</a>
                <a href="/views/subscriptions/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Abonnements</a>
                <a href="/views/subscriptions/plans/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Plans SaaS</a>
                <a href="/views/subscriptions/invoices/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Factures SaaS</a>
                <a href="/views/subscriptions/payments/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Paiements SaaS</a>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'users.manage') || has_permission($pdo, 'roles.manage')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Administration</p>
                <?php if (has_permission($pdo, 'users.manage')): ?>
                    <a href="/views/users/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Utilisateurs</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'roles.manage')): ?>
                    <a href="/views/roles/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Rôles</a>
                    <a href="/views/permissions/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Permissions</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'agents.view') || has_permission($pdo, 'agents.manage') || has_permission($pdo, 'agencies.manage') || has_permission($pdo, 'supervisors.manage')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Gestion</p>
                <?php if (has_permission($pdo, 'agencies.manage')): ?>
                    <a href="/views/agencies/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Agences</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'supervisors.manage')): ?>
                    <a href="/views/supervisors/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Superviseurs</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'agents.view') || has_permission($pdo, 'agents.manage')): ?>
                    <a href="/views/agents.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Agents</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'fiches.create') || has_permission($pdo, 'fiches.view') || has_permission($pdo, 'lotteries.manage') || has_permission($pdo, 'tirages.manage') || has_permission($pdo, 'gains.view')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Jeux</p>
                <?php if (has_permission($pdo, 'fiches.create')): ?>
                    <a href="/views/fiche_create.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Nouvelle fiche</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'fiches.view')): ?>
                    <a href="/views/fiches.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Fiches</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'lotteries.manage') || has_permission($pdo, 'tirages.manage')): ?>
                    <a href="/views/lotteries/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Lotteries</a>
                    <?php if (has_permission($pdo, 'lottery_schedules.manage')): ?>
                        <a href="/views/lotteries/schedules.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Horaires lotteries</a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'tirages.manage')): ?>
                    <a href="/views/tirages.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Tirages</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'gains.view')): ?>
                    <a href="/views/gagnants.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Gagnants</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'controls.manage') || has_permission($pdo, 'risk.view')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Contrôle</p>
                <?php if (has_permission($pdo, 'risk.view')): ?>
                    <a href="/views/risk/dashboard.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Dashboard risque</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'controls.manage')): ?>
                    <a href="/views/limites/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Limites boules</a>
                    <a href="/views/blocages/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Blocages</a>
                    <a href="/views/marriages/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Mariages</a>
                    <a href="/views/primes/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Primes</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'finances.manage')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Finances</p>
                <a href="/views/finances/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Dashboard finances</a>
                <a href="/views/finances/agents.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Balances agents</a>
                <a href="/views/finances/transactions.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Transactions</a>
                <?php if (has_permission($pdo, 'commissions.manage')): ?>
                    <a href="/views/commissions/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Commissions</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'cash_sessions.manage') && !has_permission($pdo, 'finances.manage')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Opérations</p>
                <a href="/views/cash_sessions/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Sessions caisse</a>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'finances.manage') && has_permission($pdo, 'cash_sessions.manage')): ?>
                <a href="/views/cash_sessions/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Sessions caisse</a>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'reports.view')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Rapports</p>
                <a href="/views/rapports/daily.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Journalier</a>
                <a href="/views/rapports/monthly.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Mensuel</a>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'notifications.view')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Workflow</p>
                <a href="/views/notifications/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Notifications</a>
            <?php endif; ?>

            <?php if (has_permission($pdo, 'settings.manage')): ?>
                <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Système</p>
                <a href="/views/settings/tenant.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Paramètres tenant</a>
                <?php if (has_permission($pdo, 'system.settings')): ?>
                    <a href="/views/settings/system.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Configuration système</a>
                <?php endif; ?>
                <?php if (has_permission($pdo, 'health.view')): ?>
                    <a href="/views/settings/health.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Santé système</a>
                <?php endif; ?>
                <a href="/views/settings/backups.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Sauvegardes</a>
                <?php if (has_permission($pdo, 'logs.view')): ?>
                    <a href="/views/logs/index.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Journal d’audit</a>
                <?php endif; ?>
            <?php endif; ?>

            <p class="text-gray-400 text-xs mt-5 uppercase tracking-wide">Compte</p>
            <a href="/views/users/change_password.php" class="block px-4 py-3 rounded hover:bg-yellow-500 hover:text-black transition">Changer mot de passe</a>
            <a href="/actions/logout.php" class="block px-4 py-3 rounded bg-red-600 hover:bg-red-700 transition mt-3">Déconnexion</a>
        </nav>
        <?php
    }
}
?>

<aside class="hidden md:flex md:flex-col w-64 bg-black text-white min-h-screen p-5 sticky top-0 h-screen overflow-y-auto">
    <h1 class="text-2xl font-bold mb-8 text-yellow-400">MCS LOTTO</h1>
    <?php render_sidebar_nav($pdo); ?>
</aside>

<div id="mobileSidebarBackdrop" class="fixed inset-0 bg-black/60 z-40 hidden md:hidden" onclick="closeMobileSidebar()"></div>

<aside id="mobileSidebar" class="fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] bg-black text-white p-5 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden overflow-y-auto shadow-2xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-yellow-400">MCS LOTTO</h1>
        <button type="button" onclick="closeMobileSidebar()" class="text-white bg-white/10 hover:bg-white/20 rounded-lg px-3 py-2" aria-label="Fermer le menu">
            ✕
        </button>
    </div>
    <?php render_sidebar_nav($pdo); ?>
</aside>

<script>
function openMobileSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const backdrop = document.getElementById('mobileSidebarBackdrop');
    if (!sidebar || !backdrop) return;
    sidebar.classList.remove('-translate-x-full');
    backdrop.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const backdrop = document.getElementById('mobileSidebarBackdrop');
    if (!sidebar || !backdrop) return;
    sidebar.classList.add('-translate-x-full');
    backdrop.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeMobileSidebar();
    }
});
</script>
