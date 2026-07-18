<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../app/Helpers/security.php';
send_security_headers(false);
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - MCS Lotto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
    <h1 class="text-3xl font-bold text-center mb-2">MCS LOTTO</h1>
    <p class="text-center text-gray-500 mb-6">Connectez-vous pour continuer</p>
    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= e($error) ?></div>
    <?php endif; ?>
    <form action="../actions/login.php" method="POST">
        <?= csrf_field() ?>
        <input type="text" name="username" placeholder="Identifiant" class="w-full border rounded-lg p-3 mb-4" required>
        <input type="password" name="password" placeholder="Mot de passe" class="w-full border rounded-lg p-3 mb-6" required>
        <button class="w-full bg-black text-white py-3 rounded-lg hover:bg-gray-800">Se connecter</button>
    </form>
    <div class="text-center mt-4"><a class="text-blue-600" href="tenant_register.php">Créer un nouveau tenant / banque</a></div>
</div>
</body>
</html>
