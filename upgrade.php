<?php
require __DIR__ . '/config/database.php';
$migrationDir = __DIR__ . '/database/migrations';
$pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (id INT AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) UNIQUE, executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
$done = $pdo->query("SELECT migration FROM schema_migrations")->fetchAll(PDO::FETCH_COLUMN);
$files = glob($migrationDir.'/*.sql');
sort($files);
$ran = [];
$errors = [];
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $done, true)) { continue; }
    try {
        $pdo->beginTransaction();
        $pdo->exec(file_get_contents($file));
        $stmt = $pdo->prepare("INSERT INTO schema_migrations(migration) VALUES (?)");
        $stmt->execute([$name]);
        $pdo->commit();
        $ran[] = $name;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $errors[] = $name . ': ' . $e->getMessage();
        break;
    }
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Upgrade</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 p-6"><div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">
<h1 class="text-2xl font-bold mb-4">Mise à jour système</h1>
<?php if ($errors): ?><div class="bg-red-100 text-red-800 p-4 rounded"><?= htmlspecialchars(implode("\n", $errors)) ?></div><?php else: ?><div class="bg-green-100 text-green-800 p-4 rounded">Mise à jour terminée.</div><?php endif; ?>
<h2 class="font-bold mt-4">Migrations exécutées</h2><ul><?php foreach($ran as $r): ?><li>✓ <?= htmlspecialchars($r) ?></li><?php endforeach; ?><?php if(!$ran): ?><li>Aucune nouvelle migration.</li><?php endif; ?></ul>
</div></body></html>
