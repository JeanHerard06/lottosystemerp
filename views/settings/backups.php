<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Core/Autoload.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';

require_permission($pdo, 'settings.manage');
$backups = (new BackupService($pdo, dirname(__DIR__, 2)))->list();
$success = $_SESSION['flash_success'] ?? null;
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div><h1 class="text-2xl font-bold">Centre de sauvegarde</h1><p class="text-gray-500">Sauvegardes SQL sécurisées avec empreinte SHA-256.</p></div>
    <form method="POST" action="/actions/backups/create.php">
        <?= csrf_field() ?>
        <button class="bg-black text-white px-5 py-3 rounded-lg font-semibold hover:bg-gray-800">Créer une sauvegarde</button>
    </form>
</div>
<?php if ($success): ?><div class="bg-green-100 text-green-800 rounded-lg p-4 mb-4"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bg-red-100 text-red-800 rounded-lg p-4 mb-4"><?= e($error) ?></div><?php endif; ?>
<div class="bg-white rounded-xl shadow overflow-hidden">
<table class="w-full responsive-table">
<thead><tr class="bg-gray-100 text-left"><th class="p-3">Fichier</th><th class="p-3">Taille</th><th class="p-3">Créé</th><th class="p-3">SHA-256</th><th class="p-3">Action</th></tr></thead>
<tbody>
<?php if (!$backups): ?><tr><td colspan="5" class="p-8 text-center text-gray-500">Aucune sauvegarde disponible.</td></tr><?php endif; ?>
<?php foreach ($backups as $backup): ?>
<tr class="border-t">
    <td data-label="Fichier" class="p-3 font-medium"><?= e($backup['filename']) ?></td>
    <td data-label="Taille" class="p-3"><?= e(number_format(((int)$backup['size']) / 1024, 2)) ?> KB</td>
    <td data-label="Créé" class="p-3"><?= e($backup['created_at']) ?></td>
    <td data-label="SHA-256" class="p-3 font-mono text-xs break-all"><?= e($backup['sha256']) ?></td>
    <td data-label="Action" class="p-3"><a class="text-blue-600 font-semibold" href="/actions/backups/download.php?file=<?= urlencode($backup['filename']) ?>">Télécharger</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div class="mt-4 bg-yellow-50 text-yellow-900 rounded-lg p-4 text-sm">La restauration automatique reste volontairement désactivée dans RC1.4. Une restauration doit être validée sur un environnement de test avant toute exécution en production.</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
