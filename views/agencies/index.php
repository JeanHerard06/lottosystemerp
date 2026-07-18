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
$totalAgents = array_sum(array_map(fn($agency) => (int)$agency['total_agents'], $agencies));

ui_page_header('Agences', 'Les utilisateurs tenant voient uniquement les agences de leur organisation.', [
    ['label' => 'Ajouter agence', 'href' => 'create.php', 'class' => 'ui-btn ui-btn-warning', 'icon' => '+'],
]);
?>
<div class="ui-stat-grid">
    <?php ui_stat_card('Agences', (string)count($agencies), 'blue', 'Dans le périmètre visible', null, '🏢'); ?>
    <?php ui_stat_card('Agents rattachés', (string)$totalAgents, 'green', 'Total des équipes', null, '👥'); ?>
    <?php ui_stat_card('Moyenne', count($agencies) > 0 ? number_format($totalAgents / count($agencies), 1) : '0', 'slate', 'Agents par agence', null, '∅'); ?>
</div>

<div class="ui-table-panel">
<table class="w-full">
    <thead>
        <tr class="text-left">
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
            <td class="p-3"><?= ui_status_badge((string)$agency['status']) ?></td>
            <td class="p-3"><?= ui_action_link('Modifier', 'edit.php?id=' . (int)$agency['id'], 'secondary') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$agencies): ?><tr><td colspan="<?= is_super_admin() ? 7 : 6 ?>"><?php ui_empty_state('Aucune agence trouvée', 'Créez une agence pour organiser vos agents.', '🏢'); ?></td></tr><?php endif; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
