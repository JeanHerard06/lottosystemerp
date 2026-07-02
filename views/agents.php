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
?>
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Agents</h1>
        <p class="text-gray-500">Gestion complète des agents, agences, commissions et statuts.</p>
    </div>
    <?php if (has_permission($pdo, 'agents.manage')): ?>
        <a href="agent_create.php" class="bg-yellow-500 text-white px-4 py-2 rounded text-center">+ Ajouter agent</a>
    <?php endif; ?>
</div>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full min-w-[900px]">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="p-3">Nom</th>
                <th class="p-3">Identifiant</th>
                <th class="p-3">Agence</th>
                <th class="p-3">Téléphone</th>
                <th class="p-3">Commission</th>
                <th class="p-3">Balance</th>
                <th class="p-3">Statut</th>
                <th class="p-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($agents as $agent): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-3 font-medium"><?= e($agent['name']) ?></td>
                <td class="p-3"><?= e($agent['username']) ?></td>
                <td class="p-3"><?= e(trim(($agent['agency_code'] ?? '') . ' ' . ($agent['agency_name'] ?? '')) ?: '-') ?></td>
                <td class="p-3"><?= e($agent['phone']) ?></td>
                <td class="p-3"><?= number_format((float)$agent['commission'], 2) ?>%</td>
                <td class="p-3"><?= number_format((float)$agent['balance'], 2) ?></td>
                <td class="p-3">
                    <?php if ((int)$agent['status'] === 1): ?>
                        <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-sm">Actif</span>
                    <?php else: ?>
                        <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-sm">Inactif</span>
                    <?php endif; ?>
                </td>
                <td class="p-3 text-right whitespace-nowrap">
                    <?php if (has_permission($pdo, 'agents.manage')): ?>
                        <a href="agent_edit.php?id=<?= (int)$agent['id'] ?>" class="text-blue-600 hover:underline mr-3">Modifier</a>
                        <form action="../actions/agent_toggle.php" method="POST" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$agent['id'] ?>">
                            <button class="text-orange-600 hover:underline mr-3" onclick="return confirm('Changer le statut de cet agent ?')">
                                <?= ((int)$agent['status'] === 1) ? 'Désactiver' : 'Activer' ?>
                            </button>
                        </form>
                        <form action="../actions/agent_delete.php" method="POST" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$agent['id'] ?>">
                            <button class="text-red-600 hover:underline" onclick="return confirm('Supprimer cet agent ? Si l’agent a déjà des fiches, il sera seulement désactivé.')">Supprimer</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$agents): ?>
            <tr><td class="p-5 text-center text-gray-500" colspan="8">Aucun agent trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
