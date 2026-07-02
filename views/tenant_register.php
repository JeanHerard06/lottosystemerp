<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../app/Helpers/security.php';
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Demande tenant - MCS Lotto</title><script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-2xl bg-white rounded-xl shadow p-8">
  <h1 class="text-3xl font-bold mb-2">Demande d'inscription tenant</h1>
  <p class="text-gray-500 mb-6">Nouvelle banque/opérateur bòlèt: remplissez le formulaire. Un super_admin doit approuver la demande.</p>
  <?php if ($error): ?><div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= e($success) ?></div><?php endif; ?>
  <form action="../actions/tenant_register_store.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <input name="business_name" class="border p-3 rounded md:col-span-2" placeholder="Nom de la banque / business" required>
    <input name="owner_name" class="border p-3 rounded" placeholder="Nom responsable" required>
    <input name="email" type="email" class="border p-3 rounded" placeholder="Email" required>
    <input name="phone" class="border p-3 rounded" placeholder="Téléphone" required>
    <select name="plan" class="border p-3 rounded" required><option value="basic">Basic</option><option value="pro">Pro</option><option value="enterprise">Enterprise</option></select>
    <input name="address" class="border p-3 rounded md:col-span-2" placeholder="Adresse">
    <textarea name="notes" class="border p-3 rounded md:col-span-2" placeholder="Notes / besoins" rows="4"></textarea>
    <div class="md:col-span-2 flex justify-between items-center">
      <a href="login.php" class="text-blue-600">Déjà approuvé? Connexion</a>
      <button class="bg-black text-white px-6 py-3 rounded">Envoyer la demande</button>
    </div>
  </form>
</div>
</body></html>
