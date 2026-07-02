<?php
require "../../config/database.php";
require "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_auth();
require_tenant_active($pdo);
require "../../includes/sidebar.php";
require "../../includes/topbar.php";
?>
<div class="max-w-xl">
    <h1 class="text-2xl font-bold mb-2">Changer mon mot de passe</h1>
    <p class="text-gray-500 mb-5">Pour votre sécurité, entrez votre mot de passe actuel avant de définir le nouveau.</p>

    <?php if (!empty($_GET['success'])): ?>
        <div class="bg-green-100 text-green-800 border border-green-200 rounded p-3 mb-4">Mot de passe modifié avec succès.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="bg-red-100 text-red-800 border border-red-200 rounded p-3 mb-4"><?= e($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="../../actions/users/change_password.php" method="post" class="bg-white rounded-xl shadow p-5">
        <?= csrf_field() ?>
        <label class="block font-semibold mb-1">Mot de passe actuel</label>
        <input name="current_password" type="password" class="w-full border p-3 rounded mb-3" required>

        <label class="block font-semibold mb-1">Nouveau mot de passe</label>
        <input name="new_password" type="password" class="w-full border p-3 rounded mb-3" minlength="8" required>

        <label class="block font-semibold mb-1">Confirmer le nouveau mot de passe</label>
        <input name="confirm_password" type="password" class="w-full border p-3 rounded mb-4" minlength="8" required>

        <button class="bg-black text-white px-5 py-3 rounded">Mettre à jour</button>
    </form>
</div>
<?php require "../../includes/footer.php"; ?>
