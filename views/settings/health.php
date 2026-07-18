<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Core/Autoload.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';

require_permission($pdo, 'health.view');
$health = (new HealthService($pdo, dirname(__DIR__, 2)))->summary();
$statusClasses = [
    'healthy' => 'bg-green-100 text-green-700',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'critical' => 'bg-red-100 text-red-700',
];
$statusLabels = ['healthy' => 'Opérationnel', 'warning' => 'Attention', 'critical' => 'Critique'];
?>
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Santé système</h1>
        <p class="text-gray-500">État opérationnel des services essentiels de la plateforme.</p>
    </div>
    <span class="inline-flex self-start px-3 py-2 rounded-full text-sm font-semibold <?= $statusClasses[$health['status']] ?>">
        <?= e($statusLabels[$health['status']]) ?>
    </span>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-5"><p class="text-sm text-gray-500">Services opérationnels</p><p class="text-3xl font-bold text-green-600"><?= (int)$health['counts']['healthy'] ?></p></div>
    <div class="bg-white rounded-xl shadow p-5"><p class="text-sm text-gray-500">Avertissements</p><p class="text-3xl font-bold text-yellow-600"><?= (int)$health['counts']['warning'] ?></p></div>
    <div class="bg-white rounded-xl shadow p-5"><p class="text-sm text-gray-500">Critiques</p><p class="text-3xl font-bold text-red-600"><?= (int)$health['counts']['critical'] ?></p></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
<?php foreach ($health['checks'] as $check): ?>
    <article class="bg-white rounded-xl shadow p-5 border-l-4 <?= $check['status'] === 'healthy' ? 'border-green-500' : ($check['status'] === 'warning' ? 'border-yellow-500' : 'border-red-500') ?>">
        <div class="flex items-start justify-between gap-3">
            <h2 class="font-semibold"><?= e($check['label']) ?></h2>
            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusClasses[$check['status']] ?>"><?= e($statusLabels[$check['status']]) ?></span>
        </div>
        <p class="text-sm text-gray-500 mt-3 break-words"><?= e($check['message']) ?></p>
    </article>
<?php endforeach; ?>
</div>

<p class="text-xs text-gray-400 mt-5">Rapport généré le <?= e($health['generated_at']) ?></p>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
