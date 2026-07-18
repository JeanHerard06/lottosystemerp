<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Core/Autoload.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_permission($pdo, 'system.settings');
$info = (new VersionService(dirname(__DIR__, 2)))->information();
?>
<h1 class="text-2xl font-bold mb-1">Déploiement et version</h1>
<p class="text-gray-500 mb-6">Informations de version utiles pour les mises à jour et le support.</p>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
<?php foreach ([
    'Version application' => $info['application_version'],
    'Version PHP' => $info['php_version'],
    'Environnement' => $info['environment'],
    'Migrations détectées' => $info['migration_count'],
] as $label => $value): ?>
<div class="bg-white rounded-xl shadow p-5"><p class="text-sm text-gray-500"><?= e($label) ?></p><p class="text-xl font-bold mt-2 break-words"><?= e($value) ?></p></div>
<?php endforeach; ?>
</div>
<div class="bg-white rounded-xl shadow p-5 mt-5"><p class="text-sm text-gray-500">Dernière migration disponible</p><p class="font-mono mt-2 break-all"><?= e($info['latest_migration'] ?? 'Aucune') ?></p></div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
