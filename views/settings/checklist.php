<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';

require_permission($pdo, 'health.view');

function qa_status(bool $ok): string { return $ok ? 'OK' : 'À vérifier'; }
function qa_badge(bool $ok): string { return $ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; }
function qa_current_database(PDO $pdo): ?string {
    try {
        $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
        return $db ? (string)$db : null;
    } catch (Throwable $e) {
        return null;
    }
}
function qa_table_exists(PDO $pdo, string $table): bool {
    try {
        $db = qa_current_database($pdo);
        if (!$db) return false;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $stmt->execute([$db, $table]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}
function qa_count(PDO $pdo, string $table): ?int {
    try {
        if (!qa_table_exists($pdo, $table)) return null;
        $safeTable = str_replace('`', '``', $table);
        return (int)$pdo->query("SELECT COUNT(*) FROM `{$safeTable}`")->fetchColumn();
    } catch (Throwable $e) {
        return null;
    }
}
function qa_column_exists(PDO $pdo, string $table, string $column): bool {
    try {
        $db = qa_current_database($pdo);
        if (!$db) return false;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$db, $table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$dbOk = true;
try { $pdo->query('SELECT 1'); } catch (Throwable $e) { $dbOk = false; }
$currentDb = $dbOk ? qa_current_database($pdo) : null;

$requiredTables = [
    'users' => 'users',
    'tenants' => 'tenants',
    'agencies' => 'agencies',
    'agents' => 'agents',
    'lotteries' => 'lotteries',
    'fiches' => 'fiches',
    'fiche_details' => 'fiche details',
    'cash_sessions' => 'cash sessions',
    'gains' => 'gains',
    'tirages' => 'tirages',
    'notifications' => 'notifications',
];

$checks = [
    ['section' => 'Système', 'name' => 'Connexion database', 'ok' => $dbOk, 'detail' => $dbOk ? 'PDO OK' : 'Database inaccessible'],
    ['section' => 'Système', 'name' => 'Database sélectionnée', 'ok' => !empty($currentDb), 'detail' => $currentDb ?: 'Aucune database sélectionnée dans config/.env'],
    ['section' => 'Système', 'name' => 'PHP >= 8.1', 'ok' => version_compare(PHP_VERSION, '8.1.0', '>='), 'detail' => PHP_VERSION],
    ['section' => 'Système', 'name' => 'Extension PDO MySQL', 'ok' => extension_loaded('pdo_mysql'), 'detail' => extension_loaded('pdo_mysql') ? 'Chargée' : 'Manquante'],
    ['section' => 'Système', 'name' => 'Extension OpenSSL', 'ok' => extension_loaded('openssl'), 'detail' => extension_loaded('openssl') ? 'Chargée' : 'Manquante'],
    ['section' => 'Système', 'name' => 'Storage writable', 'ok' => is_dir(__DIR__ . '/../../storage') && is_writable(__DIR__ . '/../../storage'), 'detail' => 'storage/'],
];

foreach ($requiredTables as $table => $label) {
    $exists = qa_table_exists($pdo, $table);
    $count = $exists ? qa_count($pdo, $table) : null;
    $checks[] = ['section' => 'Database', 'name' => 'Table ' . $table, 'ok' => $exists, 'detail' => $label . ': ' . ($exists ? (string)$count : 'table manquante')];
}

$tenantTables = ['users', 'agencies', 'agents', 'lotteries', 'fiches', 'gains'];
foreach ($tenantTables as $table) {
    if (qa_table_exists($pdo, $table)) {
        $checks[] = ['section' => 'Tenant', 'name' => $table . '.tenant_id', 'ok' => qa_column_exists($pdo, $table, 'tenant_id'), 'detail' => 'Isolation tenant'];
    }
}

$checks = array_merge($checks, [
    ['section' => 'Mobile API', 'name' => 'Endpoint login mobile', 'ok' => file_exists(__DIR__ . '/../../api/mobile/login.php'), 'detail' => '/api/mobile/login.php'],
    ['section' => 'Mobile API', 'name' => 'Endpoint dashboard mobile', 'ok' => file_exists(__DIR__ . '/../../api/mobile/dashboard.php'), 'detail' => '/api/mobile/dashboard.php'],
    ['section' => 'Mobile API', 'name' => 'Endpoint lotteries mobile', 'ok' => file_exists(__DIR__ . '/../../api/mobile/lotteries_list.php'), 'detail' => '/api/mobile/lotteries_list.php'],
    ['section' => 'Mobile API', 'name' => 'Endpoint tirages mobile', 'ok' => file_exists(__DIR__ . '/../../api/mobile/tirages_list.php'), 'detail' => '/api/mobile/tirages_list.php'],
    ['section' => 'Mobile App', 'name' => 'Flutter pubspec', 'ok' => file_exists(__DIR__ . '/../../mobile_app/pubspec.yaml'), 'detail' => 'mobile_app/pubspec.yaml'],
]);

$passed = count(array_filter($checks, fn($c) => $c['ok']));
$total = count($checks);
?>
<h1 class="text-2xl font-bold mb-2">Checklist RC / QA</h1>
<p class="text-gray-600 mb-5">Vérification rapide de l’installation web, database et mobile agent.</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Checks réussis</p>
        <h2 class="text-3xl font-bold text-green-700"><?= $passed ?>/<?= $total ?></h2>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">À vérifier</p>
        <h2 class="text-3xl font-bold text-red-700"><?= $total - $passed ?></h2>
    </div>
    <div class="bg-white p-5 rounded shadow">
        <p class="text-gray-500">Database</p>
        <h2 class="text-xl font-bold <?= $currentDb ? 'text-green-700' : 'text-red-700' ?>"><?= e($currentDb ?: 'Non sélectionnée') ?></h2>
    </div>
</div>

<?php if (!$currentDb): ?>
<div class="mb-5 bg-red-50 border border-red-200 text-red-800 p-4 rounded">
    La connexion PDO fonctionne peut-être, mais aucune base de données n’est sélectionnée. Vérifie <code>.env</code> / <code>config/database.php</code> et confirme que <code>DB_NAME</code> pointe vers la base installée.
</div>
<?php endif; ?>

<table class="w-full bg-white rounded shadow responsive-table">
    <thead>
    <tr class="bg-gray-200 text-left">
        <th class="p-3">Section</th>
        <th class="p-3">Check</th>
        <th class="p-3">Statut</th>
        <th class="p-3">Détail</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($checks as $check): ?>
        <tr class="border-b">
            <td data-label="Section" class="p-3"><?= e($check['section']) ?></td>
            <td data-label="Check" class="p-3 font-semibold"><?= e($check['name']) ?></td>
            <td data-label="Statut" class="p-3"><span class="px-3 py-1 rounded-full text-xs font-bold <?= qa_badge($check['ok']) ?>"><?= qa_status($check['ok']) ?></span></td>
            <td data-label="Détail" class="p-3 text-gray-600"><?= e((string)$check['detail']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="mt-6 bg-white p-5 rounded shadow">
    <h2 class="text-xl font-bold mb-3">Workflow navigateur à tester</h2>
    <ol class="list-decimal list-inside space-y-1 text-gray-700">
        <li>Login admin.</li>
        <li>Créer tenant, agence, agent.</li>
        <li>Créer lottery ouverte + tirage.</li>
        <li>Ouvrir cash session pour agent.</li>
        <li>Créer fiche web et imprimer ticket.</li>
        <li>Tester login mobile agent, charger lotteries/tirages, créer fiche.</li>
        <li>Scanner/verify ticket et soumettre claim si gagnant.</li>
    </ol>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
