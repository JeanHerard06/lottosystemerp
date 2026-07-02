<?php
require "../../config/database.php";
require "../../includes/header.php";
require "../../includes/sidebar.php";
require "../../includes/topbar.php";
require_once "../../app/Helpers/tenant.php";
require_once "../../app/Helpers/security.php";
require_once "../../app/Helpers/csrf.php";
require_super_admin();
?>
<h1 class="text-2xl font-bold mb-5">Nouveau tenant</h1>
<form action="../../actions/tenants/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-xl">
    <?= csrf_field() ?>
    <input name="name" class="w-full border p-3 rounded mb-3" placeholder="Nom banque / opérateur" required>
    <input name="slug" class="w-full border p-3 rounded mb-3" placeholder="slug unique ex: bank-demo" required>
    <select name="plan" class="w-full border p-3 rounded mb-3">
        <option value="basic">Basic</option>
        <option value="pro">Pro</option>
        <option value="enterprise">Enterprise</option>
    </select>
    <input type="date" name="expires_at" class="w-full border p-3 rounded mb-3">
    <textarea name="notes" class="w-full border p-3 rounded mb-3" placeholder="Notes internes"></textarea>
    <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require "../../includes/footer.php"; ?>
