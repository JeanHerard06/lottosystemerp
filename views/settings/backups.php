<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
$dir = __DIR__ . '/../../storage/backups'; if(!is_dir($dir)) mkdir($dir,0775,true);
$files = array_values(array_filter(scandir($dir), fn($f)=>str_ends_with($f,'.sql')));
rsort($files);
?>
<div class="flex justify-between mb-5"><h1 class="text-2xl font-bold">Sauvegardes</h1><a href="/actions/backups/create.php" class="bg-black text-white px-4 py-2 rounded">Créer backup</a></div>
<table class="w-full bg-white rounded shadow"><thead><tr class="bg-gray-200 text-left"><th class="p-3">Fichier</th><th class="p-3">Taille</th><th class="p-3">Date</th></tr></thead><tbody>
<?php foreach($files as $f): $p=$dir.'/'.$f; ?><tr class="border-b"><td class="p-3"><?= htmlspecialchars($f) ?></td><td class="p-3"><?= number_format(filesize($p)/1024,2) ?> KB</td><td class="p-3"><?= date('Y-m-d H:i:s', filemtime($p)) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
