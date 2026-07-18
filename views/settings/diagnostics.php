<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';

require_permission($pdo, 'health.view');
$report = $_SESSION['last_diagnostic_report'] ?? null;
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$statusClass = fn(string $status): string => $status === 'healthy' ? 'text-green-700 bg-green-100' : ($status === 'warning' ? 'text-yellow-800 bg-yellow-100' : 'text-red-700 bg-red-100');
?>
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold">Diagnostics système</h1>
        <p class="text-gray-500">Vérification automatisée de la base, du stockage, des migrations et des composants critiques.</p>
    </div>
    <form method="POST" action="/actions/diagnostics/run.php">
        <?= csrf_field() ?>
        <button class="bg-black text-white px-5 py-3 rounded-lg font-semibold hover:bg-gray-800">Exécuter les diagnostics</button>
    </form>
</div>
<?php if ($success): ?><div class="bg-green-100 text-green-800 rounded-lg p-4 mb-4"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-100 text-red-800 rounded-lg p-4 mb-4"><?= e($error) ?></div><?php endif; ?>

<?php if ($report): ?>
<div class="bg-white rounded-xl shadow p-5 mb-5 flex flex-wrap gap-5">
    <div><p class="text-xs text-gray-500">État général</p><span class="inline-flex mt-1 px-3 py-1 rounded-full font-semibold <?= $statusClass($report['status']) ?>"><?= e(strtoupper($report['status'])) ?></span></div>
    <div><p class="text-xs text-gray-500">PHP</p><p class="font-semibold"><?= e($report['php_version']) ?></p></div>
    <div><p class="text-xs text-gray-500">Timezone</p><p class="font-semibold"><?= e($report['timezone']) ?></p></div>
    <div><p class="text-xs text-gray-500">Généré</p><p class="font-semibold"><?= e($report['generated_at']) ?></p></div>
</div>
<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full responsive-table">
        <thead><tr class="bg-gray-100 text-left"><th class="p-3">Test</th><th class="p-3">État</th><th class="p-3">Résultat</th></tr></thead>
        <tbody>
        <?php foreach ($report['tests'] as $test): ?>
            <tr class="border-t"><td data-label="Test" class="p-3 font-medium"><?= e($test['label']) ?></td><td data-label="État" class="p-3"><span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusClass($test['status']) ?>"><?= e($test['status']) ?></span></td><td data-label="Résultat" class="p-3 text-sm text-gray-600 break-words"><?= e($test['message']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="bg-white rounded-xl shadow p-8 text-center text-gray-500">Aucun diagnostic exécuté pendant cette session.</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
