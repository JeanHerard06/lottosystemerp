<?php
require_once __DIR__ . '/app/Helpers/migrations.php';

$errors = [];
$ok = [];
$installed = false;
$ran = [];
$warnings = [];

if (version_compare(PHP_VERSION, '8.0.0', '>=')) { $ok[] = 'PHP ' . PHP_VERSION; } else { $errors[] = 'PHP 8.0+ requis'; }
foreach (['pdo','pdo_mysql','json','session','openssl'] as $ext) {
    if (extension_loaded($ext)) { $ok[] = "Extension {$ext} OK"; } else { $errors[] = "Extension {$ext} manquante"; }
}
foreach (['storage','storage/backups','public/uploads'] as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) { @mkdir($path, 0775, true); }
    if (is_writable($path)) { $ok[] = "{$dir} writable"; } else { $errors[] = "{$dir} doit être writable"; }
}
if (is_dir(__DIR__ . '/database/migrations')) { $ok[] = 'Migrations trouvées'; } else { $errors[] = 'database/migrations introuvable'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $port = trim($_POST['db_port'] ?? '3306');
    $db = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['db_name'] ?? 'lotto_system');
    $user = trim($_POST['db_user'] ?? 'root');
    $pass = (string)($_POST['db_pass'] ?? '');
    $adminUser = trim($_POST['admin_username'] ?? 'admin');
    $adminPass = (string)($_POST['admin_password'] ?? 'admin123');

    if ($db === '') { $errors[] = 'Nom base de données invalide.'; }
    if ($adminUser === '') { $errors[] = 'Utilisateur admin requis.'; }
    if (strlen($adminPass) < 8) { $errors[] = 'Mot de passe admin: minimum 8 caractères.'; }

    if (!$errors) {
        try {
            $pdoServer = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdoServer->exec("USE `{$db}`");

            $result = lotto_run_migrations($pdoServer, __DIR__ . '/database/migrations', $db, true);
            $ran = $result['ran'];
            $warnings = $result['warnings'];

            // Ensure the platform admin exists with the requested password.
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdoServer->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
            $stmt->execute([$adminUser]);
            $adminId = $stmt->fetchColumn();
            if ($adminId) {
                $stmt = $pdoServer->prepare("UPDATE users SET password=?, role='super_admin', tenant_id=NULL, status=1 WHERE id=?");
                $stmt->execute([$hash, $adminId]);
            } else {
                $stmt = $pdoServer->prepare("INSERT INTO users(name, username, password, role, status, tenant_id) VALUES ('Super Admin', ?, ?, 'super_admin', 1, NULL)");
                $stmt->execute([$adminUser, $hash]);
                $adminId = $pdoServer->lastInsertId();
            }
            $stmt = $pdoServer->prepare("INSERT IGNORE INTO roles(name, slug) VALUES ('Super Admin','super_admin')");
            $stmt->execute();
            $pdoServer->exec("INSERT IGNORE INTO role_permissions(role_id, permission_id) SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.slug='super_admin'");
            $pdoServer->exec("INSERT IGNORE INTO user_roles(user_id, role_id) SELECT {$adminId}, r.id FROM roles r WHERE r.slug='super_admin'");

            lotto_write_env([
                'APP_NAME' => 'Lotto ERP Enterprise',
                'APP_ENV' => 'local',
                'APP_DEBUG' => 'true',
                'APP_URL' => 'http://localhost:8081',
                'DB_HOST' => $host,
                'DB_PORT' => $port,
                'DB_DATABASE' => $db,
                'DB_USERNAME' => $user,
                'DB_PASSWORD' => $pass,
            ], __DIR__ . '/.env');

            $installed = true;
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Installation Lotto ERP</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen p-6"><div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-6">
<h1 class="text-2xl font-bold mb-4">Installation Lotto ERP Enterprise</h1>
<?php if ($installed): ?>
<div class="p-4 bg-green-100 text-green-800 rounded mb-4">Installation terminée. Login: <b><?= htmlspecialchars($adminUser) ?></b>. Protégez ou supprimez install.php après installation.</div>
<?php if($ran): ?><h2 class="font-bold mt-4">Migrations exécutées</h2><ul class="text-sm list-disc pl-5"><?php foreach($ran as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?></ul><?php endif; ?>
<?php if($warnings): ?><details class="mt-4"><summary class="cursor-pointer text-yellow-700">Avertissements non bloquants</summary><pre class="text-xs whitespace-pre-wrap bg-yellow-50 p-3 rounded"><?= htmlspecialchars(implode("\n", $warnings)) ?></pre></details><?php endif; ?>
<a class="inline-block mt-5 bg-black text-white px-4 py-2 rounded" href="views/login.php">Aller au login</a>
<?php else: ?>
<h2 class="font-bold mb-2">Vérifications</h2>
<ul class="mb-4 text-sm"><?php foreach($ok as $m): ?><li class="text-green-700">✓ <?= htmlspecialchars($m) ?></li><?php endforeach; ?><?php foreach($errors as $m): ?><li class="text-red-700">✗ <?= htmlspecialchars($m) ?></li><?php endforeach; ?></ul>
<form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-3">
<input name="db_host" value="localhost" class="border p-3 rounded" placeholder="Host">
<input name="db_port" value="3306" class="border p-3 rounded" placeholder="Port">
<input name="db_name" value="lotto_system" class="border p-3 rounded" placeholder="Database">
<input name="db_user" value="root" class="border p-3 rounded" placeholder="User">
<input name="db_pass" type="password" class="border p-3 rounded md:col-span-2" placeholder="Password MySQL">
<input name="admin_username" value="admin" class="border p-3 rounded" placeholder="Admin username">
<input name="admin_password" type="password" value="admin12345" class="border p-3 rounded" placeholder="Admin password">
<button class="bg-black text-white px-5 py-3 rounded md:col-span-2" <?= $errors ? 'disabled' : '' ?>>Installer / Réinstaller base test</button>
</form>
<p class="text-sm text-gray-500 mt-4">Attention: l'installation exécute les migrations. Utilisez une base de test/dev vide.</p>
<?php endif; ?></div></body></html>
