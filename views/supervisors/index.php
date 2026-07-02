<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_permission($pdo, 'supervisors.manage');

[$tenantSql, $tenantParams] = tenant_scope_clause('s', 'WHERE');
$tenantJoin = is_super_admin() ? ' LEFT JOIN tenants t ON t.id=s.tenant_id ' : '';
$tenantSelect = is_super_admin() ? ', t.name AS tenant_name' : '';
$sql = "
SELECT s.*, u.name, u.username, u.status, ag.name AS agency_name {$tenantSelect}
FROM supervisors s
JOIN users u ON u.id=s.user_id AND u.tenant_id=s.tenant_id
LEFT JOIN agencies ag ON ag.id=s.agency_id AND ag.tenant_id=s.tenant_id
{$tenantJoin}
{$tenantSql}
ORDER BY s.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($tenantParams);
$supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between items-center mb-5">
    <h1 class="text-2xl font-bold">Superviseurs</h1>
    <a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Ajouter superviseur</a>
</div>

<table class="w-full bg-white rounded shadow">
    <thead><tr class="bg-gray-200 text-left"><?php if(is_super_admin()): ?><th class="p-3">Tenant</th><?php endif; ?><th class="p-3">Nom</th><th class="p-3">Identifiant</th><th class="p-3">Agence</th><th class="p-3">Statut</th><th class="p-3">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($supervisors as $s): ?>
        <tr class="border-b">
            <?php if(is_super_admin()): ?><td class="p-3"><?= e($s['tenant_name'] ?? '-') ?></td><?php endif; ?>
            <td class="p-3"><?= e($s['name']) ?></td>
            <td class="p-3"><?= e($s['username']) ?></td>
            <td class="p-3"><?= e($s['agency_name'] ?? '-') ?></td>
            <td class="p-3"><?= ((int)$s['status'] === 1) ? 'Actif' : 'Inactif' ?></td>
            <td class="p-3"><a href="edit.php?id=<?= (int)$s['id'] ?>" class="text-blue-600">Modifier</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
