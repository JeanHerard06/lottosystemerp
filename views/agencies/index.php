<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_permission($pdo, 'agencies.manage');

[$tenantSql, $tenantParams] = tenant_scope_clause('ag', 'WHERE');
$tenantJoin = is_super_admin() ? ' LEFT JOIN tenants t ON t.id = ag.tenant_id ' : '';
$tenantSelect = is_super_admin() ? ', t.name AS tenant_name' : '';
$sql = "
    SELECT ag.*, COUNT(a.id) AS total_agents {$tenantSelect}
    FROM agencies ag
    LEFT JOIN agents a ON a.agency_id = ag.id AND a.tenant_id = ag.tenant_id
    {$tenantJoin}
    {$tenantSql}
    GROUP BY ag.id
    ORDER BY ag.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($tenantParams);
$agencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Agences</h1>
        <p class="text-gray-500">Les utilisateurs tenant voient uniquement les agences de leur tenant.</p>
    </div>
    <a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Ajouter agence</a>
</div>

<table class="w-full bg-white rounded shadow">
    <thead>
        <tr class="bg-gray-200 text-left">
            <?php if (is_super_admin()): ?><th class="p-3">Tenant</th><?php endif; ?>
            <th class="p-3">Code</th><th class="p-3">Nom</th><th class="p-3">Téléphone</th><th class="p-3">Agents</th><th class="p-3">Statut</th><th class="p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($agencies as $agency): ?>
        <tr class="border-b">
            <?php if (is_super_admin()): ?><td class="p-3"><?= e($agency['tenant_name'] ?? '-') ?></td><?php endif; ?>
            <td class="p-3 font-semibold"><?= e($agency['code']) ?></td>
            <td class="p-3"><?= e($agency['name']) ?></td>
            <td class="p-3"><?= e($agency['phone']) ?></td>
            <td class="p-3"><?= (int)$agency['total_agents'] ?></td>
            <td class="p-3"><?= e($agency['status']) ?></td>
            <td class="p-3"><a href="edit.php?id=<?= (int)$agency['id'] ?>" class="text-blue-600">Modifier</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
