<?php
require __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Helpers/migrations.php';

$ran = [];
$warnings = [];
$errors = [];
try {
    $result = lotto_run_migrations($pdo, __DIR__ . '/database/migrations', $dbname, false);
    $ran = $result['ran'];
    $warnings = $result['warnings'];
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Upgrade Lotto ERP</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 p-6"><div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-6">
<h1 class="text-2xl font-bold mb-4">Mise à jour Lotto ERP</h1>
<?php if ($errors): ?><div class="bg-red-100 text-red-800 p-4 rounded whitespace-pre-wrap"><?= htmlspecialchars(implode("\n", $errors)) ?></div><?php else: ?><div class="bg-green-100 text-green-800 p-4 rounded">Mise à jour terminée.</div><?php endif; ?>
<h2 class="font-bold mt-4">Migrations exécutées</h2><ul class="list-disc pl-5 text-sm"><?php foreach($ran as $r): ?><li>✓ <?= htmlspecialchars($r) ?></li><?php endforeach; ?><?php if(!$ran): ?><li>Aucune nouvelle migration.</li><?php endif; ?></ul>
<?php if($warnings): ?><details class="mt-4"><summary class="cursor-pointer text-yellow-700">Avertissements non bloquants</summary><pre class="text-xs whitespace-pre-wrap bg-yellow-50 p-3 rounded"><?= htmlspecialchars(implode("\n", $warnings)) ?></pre></details><?php endif; ?>
<a href="views/dashboard.php" class="inline-block mt-5 bg-black text-white px-4 py-2 rounded">Retour dashboard</a>
</div></body></html>
