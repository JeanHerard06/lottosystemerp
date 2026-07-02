<?php
$step = $_GET['step'] ?? 'check';
$errors = [];
$ok = [];

if (version_compare(PHP_VERSION, '8.0.0', '>=')) { $ok[] = 'PHP '.PHP_VERSION; } else { $errors[] = 'PHP 8.0+ requis'; }
foreach (['pdo','pdo_mysql','json','session'] as $ext) {
    if (extension_loaded($ext)) { $ok[] = "Extension $ext OK"; } else { $errors[] = "Extension $ext manquante"; }
}
if (is_readable(__DIR__.'/database.sql')) { $ok[] = 'database.sql trouvé'; } else { $errors[] = 'database.sql introuvable'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'] ?? 'localhost';
    $db = $_POST['db_name'] ?? 'lotto_system';
    $user = $_POST['db_user'] ?? 'root';
    $pass = $_POST['db_pass'] ?? '';
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $sql = file_get_contents(__DIR__.'/database.sql');
        $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS\s+`?\w+`?.*?;/i', "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;", $sql, 1);
        $sql = preg_replace('/USE\s+`?\w+`?;/i', "USE `$db`;", $sql, 1);
        $pdo->exec($sql);
        $config = "<?php\n$"."host = '$host';\n$"."dbname = '$db';\n$"."user = '$user';\n$"."pass = '$pass';\ntry {\n    $"."pdo = new PDO(\"mysql:host=$"."host;dbname=$"."dbname;charset=utf8mb4\", $"."user, $"."pass);\n    $"."pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n    $"."pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);\n} catch (PDOException $"."e) {\n    die('Erreur connexion: ' . $"."e->getMessage());\n}\n";
        file_put_contents(__DIR__.'/config/database.php', $config);
        $installed = true;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Installation MCS Lotto</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen p-6"><div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">
<h1 class="text-2xl font-bold mb-4">Installation MCS Lotto Enterprise</h1>
<?php if (!empty($installed)): ?>
<div class="p-4 bg-green-100 text-green-800 rounded mb-4">Installation terminée. Login: <b>admin</b> / <b>admin123</b>. Supprimez ou protégez install.php après installation.</div>
<a class="bg-black text-white px-4 py-2 rounded" href="views/login.php">Aller au login</a>
<?php else: ?>
<h2 class="font-bold mb-2">Vérifications</h2>
<ul class="mb-4"><?php foreach($ok as $m): ?><li class="text-green-700">✓ <?= htmlspecialchars($m) ?></li><?php endforeach; ?><?php foreach($errors as $m): ?><li class="text-red-700">✗ <?= htmlspecialchars($m) ?></li><?php endforeach; ?></ul>
<form method="post" class="space-y-3">
<input name="db_host" value="localhost" class="w-full border p-3 rounded" placeholder="Host">
<input name="db_name" value="lotto_system" class="w-full border p-3 rounded" placeholder="Database">
<input name="db_user" value="root" class="w-full border p-3 rounded" placeholder="User">
<input name="db_pass" type="password" class="w-full border p-3 rounded" placeholder="Password">
<button class="bg-black text-white px-5 py-3 rounded" <?= $errors ? 'disabled' : '' ?>>Installer</button>
</form>
<?php endif; ?></div></body></html>
