<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
require_permission($pdo, 'agents.view');

$scopeWhere = '';
$params = [];
[$tenantSql, $tenantParams] = tenant_scope_clause('a', 'WHERE');
if ($tenantSql) { $scopeWhere = $tenantSql; $params = array_merge($params, $tenantParams); }
if (($_SESSION['role'] ?? '') === 'superviseur') {
    $stmt = $pdo->prepare('SELECT agency_id FROM supervisors WHERE user_id=? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $agencyId = $stmt->fetchColumn();
    $scopeWhere .= ($scopeWhere ? ' AND ' : 'WHERE ') . 'a.agency_id = ?';
    $params[] = $agencyId ?: 0;
}

$sql = "
SELECT a.*, u.name, u.username, u.status, ag.name AS agency_name, ag.code AS agency_code
FROM agents a
JOIN users u ON u.id = a.user_id
LEFT JOIN agencies ag ON ag.id = a.agency_id
{$scopeWhere}
ORDER BY a.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$agents = $stmt->fetchAll();
$activeAgents = count(array_filter($agents, fn($agent) => (int)$agent['status'] === 1));

$actions = [];
if (has_permission($pdo, 'agents.manage')) {
    $actions[] = ['label' => 'Ajouter agent', 'href' => 'agent_create.php', 'class' => 'ui-btn ui-btn-warning', 'icon' => '+'];
}
ui_page_header('Agents', 'Gestion des agents, agences, commissions et statuts.', $actions);
?>
<div class="ui-stat-grid">
    <?php ui_stat_card('Agents', (string)count($agents), 'blue', 'Dans votre périmètre', null, '👥'); ?>
    <?php ui_stat_card('Actifs', (string)$activeAgents, 'green', 'Comptes opérationnels', null, '✓'); ?>
    <?php ui_stat_card('Inactifs', (string)(count($agents) - $activeAgents), 'amber', 'À vérifier', null, '⏸'); ?>
</div>

<div class="ui-table-panel">
    <table class="w-full min-w-[900px]">
        <thead>
            <tr class="text-left">
                <th class="p-3">Nom</th><th class="p-3">Identifiant</th><th class="p-3">Agence</th><th class="p-3">Téléphone</th><th class="p-3">Commission</th><th class="p-3">Balance</th><th class="p-3">Statut</th><th class="p-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($agents as $agent): ?>
            <tr class="border-b">
                <td class="p-3 font-medium"><?= e($agent['name']) ?></td>
                <td class="p-3"><?= e($agent['username']) ?></td>
                <td class="p-3"><?= e(trim(($agent['agency_code'] ?? '') . ' ' . ($agent['agency_name'] ?? '')) ?: '-') ?></td>
                <td class="p-3"><?= e($agent['phone']) ?></td>
                <td class="p-3"><?= number_format((float)$agent['commission'], 2) ?>%</td>
                <td class="p-3"><?= ui_money((float)$agent['balance']) ?></td>
                <td class="p-3"><?= ui_status_badge((int)$agent['status'] === 1 ? 'active' : 'inactive', (int)$agent['status'] === 1 ? 'Actif' : 'Inactif') ?></td>
                <td class="p-3 text-right whitespace-nowrap">
                    <?php if (has_permission($pdo, 'agents.manage')): ?>
                        <div class="flex flex-wrap justify-end gap-2">
                            <?= ui_action_link('Modifier', 'agent_edit.php?id=' . (int)$agent['id'], 'secondary') ?>
                            <form action="../actions/agent_toggle.php" method="POST" class="inline" data-no-responsive-filter="1">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$agent['id'] ?>">
                                <button class="ui-btn ui-btn-warning ui-btn-sm" data-confirm="Changer le statut de cet agent ?"><?= ((int)$agent['status'] === 1) ? 'Désactiver' : 'Activer' ?></button>
                            </form>
                            <form action="../actions/agent_delete.php" method="POST" class="inline" data-no-responsive-filter="1">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$agent['id'] ?>">
                                <button class="ui-btn ui-btn-danger ui-btn-sm" data-confirm="Supprimer cet agent ? Si l’agent a déjà des fiches, il sera seulement désactivé.">Supprimer</button>
                            </form>
                        </div>
                    <?php else: ?><span class="text-slate-400">—</span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$agents): ?>
            <tr><td colspan="8"><?php ui_empty_state('Aucun agent trouvé', 'Ajoutez un agent pour commencer.', '👤'); ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
