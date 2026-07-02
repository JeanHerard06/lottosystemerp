<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/notifications.php';

require_permission($pdo, 'notifications.view');

$status = $_GET['status'] ?? 'all';
[$where, $params] = notification_scope_clause($pdo, 'n', 'WHERE');
if ($status === 'unread') { $where .= ' AND n.read_at IS NULL'; }
if ($status === 'read') { $where .= ' AND n.read_at IS NOT NULL'; }

$stmt = $pdo->prepare("SELECT n.*, u.name AS user_name, t.name AS tenant_name
FROM notifications n
LEFT JOIN users u ON u.id=n.user_id
LEFT JOIN tenants t ON t.id=n.tenant_id
$where
ORDER BY n.id DESC
LIMIT 200");
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$typeClass = [
    'info' => 'bg-blue-50 text-blue-800 border-blue-200',
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
    'danger' => 'bg-red-50 text-red-800 border-red-200',
];
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold">Notifications</h1>
        <p class="text-gray-500">Alertes système, messages tenant et workflow opérationnel.</p>
    </div>
    <div class="flex gap-2">
        <?php if (has_permission($pdo, 'notifications.manage')): ?>
            <a href="/views/notifications/create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Notification</a>
        <?php endif; ?>
        <form method="post" action="/actions/notifications/mark_all_read.php">
            <?= csrf_field() ?>
            <button class="bg-black text-white px-4 py-2 rounded">Tout lu</button>
        </form>
    </div>
</div>

<div class="bg-white p-4 rounded shadow mb-5 flex gap-2">
    <a href="?status=all" class="px-3 py-2 rounded <?= $status==='all'?'bg-black text-white':'bg-gray-100' ?>">Toutes</a>
    <a href="?status=unread" class="px-3 py-2 rounded <?= $status==='unread'?'bg-black text-white':'bg-gray-100' ?>">Non lues</a>
    <a href="?status=read" class="px-3 py-2 rounded <?= $status==='read'?'bg-black text-white':'bg-gray-100' ?>">Lues</a>
</div>

<div class="space-y-3">
    <?php foreach ($notifications as $n): ?>
        <div class="border rounded-xl p-4 <?= $typeClass[$n['type']] ?? $typeClass['info'] ?> <?= empty($n['read_at']) ? 'font-semibold' : 'opacity-75' ?>">
            <div class="flex justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold"><?= e($n['title']) ?></h2>
                    <p class="mt-1 whitespace-pre-line"><?= e($n['message']) ?></p>
                    <p class="text-xs mt-2 opacity-70">
                        <?= e($n['created_at']) ?>
                        <?php if (is_super_admin() && !empty($n['tenant_name'])): ?> · Tenant: <?= e($n['tenant_name']) ?><?php endif; ?>
                        <?php if (!empty($n['user_name'])): ?> · User: <?= e($n['user_name']) ?><?php endif; ?>
                    </p>
                    <?php if (!empty($n['link_url'])): ?>
                        <a href="<?= e($n['link_url']) ?>" class="inline-block mt-2 underline">Ouvrir</a>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col gap-2 min-w-28">
                    <?php if (empty($n['read_at'])): ?>
                        <form method="post" action="/actions/notifications/mark_read.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                            <button class="w-full bg-white/80 text-black px-3 py-2 rounded">Marquer lu</button>
                        </form>
                    <?php endif; ?>
                    <?php if (has_permission($pdo, 'notifications.manage')): ?>
                        <form method="post" action="/actions/notifications/delete.php" onsubmit="return confirm('Supprimer cette notification ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                            <button class="w-full bg-red-600 text-white px-3 py-2 rounded">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$notifications): ?>
        <div class="bg-white rounded shadow p-8 text-center text-gray-500">Aucune notification.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
