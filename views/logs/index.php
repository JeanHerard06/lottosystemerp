<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';

require_permission($pdo, 'logs.view');

$filters = [
    'tenant_id' => $_GET['tenant_id'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'action_type' => $_GET['action_type'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

$logs = audit_logs_query($pdo, $filters);
$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query('SELECT id, name FROM tenants ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Journal d’audit</h1>
        <p class="text-gray-500 text-sm">Suivi des actions sensibles, connexions, modifications et exports.</p>
    </div>
    <?php if (has_permission($pdo, 'logs.manage')): ?>
        <form action="/actions/logs/purge.php" method="POST" onsubmit="return confirmAction('Supprimer les anciens logs ?')">
            <?= csrf_field() ?>
            <input type="hidden" name="days" value="180">
            <button class="btn bg-red-600 text-white">Purger +180 jours</button>
        </form>
    <?php endif; ?>
</div>

<form method="GET" class="bg-white p-4 rounded-xl shadow mb-5 grid grid-cols-1 md:grid-cols-6 gap-3">
    <?php if (is_super_admin()): ?>
        <select name="tenant_id" class="form-control">
            <option value="">Tous tenants</option>
            <?php foreach ($tenants as $tenant): ?>
                <option value="<?= (int)$tenant['id'] ?>" <?= (string)$filters['tenant_id'] === (string)$tenant['id'] ? 'selected' : '' ?>><?= e($tenant['name']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <input class="form-control" type="text" name="action_type" value="<?= e($filters['action_type']) ?>" placeholder="Action">
    <input class="form-control" type="number" name="user_id" value="<?= e($filters['user_id']) ?>" placeholder="ID utilisateur">
    <input class="form-control" type="date" name="date_from" value="<?= e($filters['date_from']) ?>">
    <input class="form-control" type="date" name="date_to" value="<?= e($filters['date_to']) ?>">
    <button class="btn bg-black text-white">Filtrer</button>
</form>

<div class="bg-white rounded-xl shadow overflow-hidden responsive-table">
    <table class="w-full text-sm">
        <thead>
        <tr class="bg-gray-100 text-left">
            <th class="p-3">Date</th>
            <?php if (is_super_admin()): ?><th class="p-3">Tenant</th><?php endif; ?>
            <th class="p-3">Utilisateur</th>
            <th class="p-3">Action</th>
            <th class="p-3">Description</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3 whitespace-nowrap" data-label="Date"><?= e($log['created_at']) ?></td>
                <?php if (is_super_admin()): ?>
                    <td class="p-3" data-label="Tenant"><?= e($log['tenant_name'] ?? 'Plateforme') ?></td>
                <?php endif; ?>
                <td class="p-3" data-label="Utilisateur"><?= e($log['user_name'] ?? $log['username'] ?? 'Système') ?></td>
                <td class="p-3" data-label="Action"><span class="badge bg-gray-100 text-gray-800"><?= e($log['action_type']) ?></span></td>
                <td class="p-3" data-label="Description"><?= e($log['description'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?>
            <tr><td class="p-4 text-gray-500" colspan="5">Aucun log trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
