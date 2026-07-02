<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';
require_once __DIR__ . '/../../app/Helpers/lotteries.php';
require_permission($pdo, 'lotteries.manage');

[$tenantSql, $tenantParams] = tenant_scope_clause('l', 'WHERE');

$sql = "SELECT l.*, t.name AS tenant_name,
        (SELECT COUNT(*) FROM tirages tr WHERE tr.lottery_id = l.id) AS total_tirages,
        (SELECT COUNT(*) FROM fiches f WHERE f.lottery_id = l.id) AS total_fiches
        FROM lotteries l
        LEFT JOIN tenants t ON t.id = l.tenant_id
        {$tenantSql}
        ORDER BY l.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($tenantParams);
$lotteries = $stmt->fetchAll(PDO::FETCH_ASSOC);
$lotteries = array_map(fn($l) => lottery_auto_close_if_due($pdo, $l), $lotteries);

require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Lotteries</h1>
        <p class="text-gray-500">Gestion des loteries/tirages disponibles par tenant.</p>
    </div>
    <a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Ajouter lottery</a>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full">
    <thead>
        <tr class="bg-gray-200 text-left">
            <?php if (is_super_admin()): ?><th class="p-3">Tenant</th><?php endif; ?>
            <th class="p-3">Nom</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Ventes</th>
            <th class="p-3">Heure</th>
            <th class="p-3">Fermeture</th>
            <th class="p-3">Tirages</th>
            <th class="p-3">Fiches</th>
            <th class="p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lotteries as $lottery): ?>
        <tr class="border-b">
            <?php if (is_super_admin()): ?><td class="p-3"><?= e($lottery['tenant_name'] ?? '-') ?></td><?php endif; ?>
            <td class="p-3 font-semibold"><?= e($lottery['name']) ?></td>
            <td class="p-3">
                <span class="px-2 py-1 rounded text-xs <?= (int)$lottery['status'] === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= (int)$lottery['status'] === 1 ? 'Active' : 'Inactive' ?>
                </span>
            </td>
            <td class="p-3">
                <span class="px-2 py-1 rounded text-xs <?= lottery_status_badge_class($lottery['sales_status'] ?? 'open') ?>">
                    <?= e(lottery_sales_status_label($lottery['sales_status'] ?? 'open')) ?>
                </span>
            </td>
            <td class="p-3"><?= !empty($lottery['draw_time']) ? e(substr($lottery['draw_time'], 0, 5)) : '-' ?></td>
            <td class="p-3"><?= (int)($lottery['close_before_minutes'] ?? 10) ?> min avant</td>
            <td class="p-3"><?= (int)$lottery['total_tirages'] ?></td>
            <td class="p-3"><?= (int)$lottery['total_fiches'] ?></td>
            <td class="p-3 space-y-2">
                <a href="edit.php?id=<?= (int)$lottery['id'] ?>" class="text-blue-600 font-semibold">Modifier</a>
                <?php if (has_permission($pdo, 'lotteries.close')): ?>
                    <form action="../../actions/lotteries/status.php" method="POST" class="inline-block ml-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$lottery['id'] ?>">
                        <?php if (($lottery['sales_status'] ?? 'open') === 'open'): ?>
                            <button name="action" value="closed" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">Fermer</button>
                        <?php else: ?>
                            <button name="action" value="open" class="bg-green-600 text-white px-2 py-1 rounded text-xs">Rouvrir</button>
                        <?php endif; ?>
                        <button name="action" value="drawn" class="bg-blue-600 text-white px-2 py-1 rounded text-xs" onclick="return confirm('Marquer cette lottery comme tirée ?')">Tirée</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$lotteries): ?>
        <tr><td colspan="<?= is_super_admin() ? 9 : 8 ?>" class="p-4 text-center text-gray-500">Aucune lottery trouvée.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
