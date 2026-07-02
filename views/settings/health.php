<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/system_settings.php';

require_permission($pdo, 'health.view');

$dbOk = true;
try { $pdo->query('SELECT 1'); } catch (Throwable $e) { $dbOk = false; }
$latestCrons = $pdo->query('SELECT * FROM cron_runs ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
$openSessions = (int)$pdo->query("SELECT COUNT(*) FROM cash_sessions WHERE status='open'")->fetchColumn();
$failedCrons = (int)$pdo->query("SELECT COUNT(*) FROM cron_runs WHERE status='failed' AND started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$settings = system_settings_map($pdo);
?>
<h1 class="text-2xl font-bold mb-5">Santé système</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
    <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Database</p><h2 class="text-2xl font-bold <?= $dbOk ? 'text-green-600' : 'text-red-600' ?>"><?= $dbOk ? 'OK' : 'Erreur' ?></h2></div>
    <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Sessions caisse ouvertes</p><h2 class="text-2xl font-bold"><?= $openSessions ?></h2></div>
    <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Cron failed 7 jours</p><h2 class="text-2xl font-bold"><?= $failedCrons ?></h2></div>
    <div class="bg-white p-5 rounded shadow"><p class="text-gray-500">Timezone</p><h2 class="text-lg font-bold"><?= e($settings['system.timezone'] ?? 'N/A') ?></h2></div>
</div>

<h2 class="text-xl font-bold mb-3">Derniers cron jobs</h2>
<table class="w-full bg-white rounded shadow responsive-table">
<thead><tr class="bg-gray-200 text-left"><th class="p-3">Job</th><th class="p-3">Status</th><th class="p-3">Message</th><th class="p-3">Début</th><th class="p-3">Fin</th></tr></thead>
<tbody>
<?php foreach ($latestCrons as $c): ?>
<tr class="border-b"><td data-label="Job" class="p-3"><?= e($c['job_name']) ?></td><td data-label="Status" class="p-3"><?= e($c['status']) ?></td><td data-label="Message" class="p-3"><?= e($c['message']) ?></td><td data-label="Début" class="p-3"><?= e($c['started_at']) ?></td><td data-label="Fin" class="p-3"><?= e($c['finished_at']) ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
