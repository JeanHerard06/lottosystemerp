<?php
require "../../../config/database.php";
require "../../../includes/header.php";
require "../../../includes/sidebar.php";
require "../../../includes/topbar.php";
require_once "../../../app/Helpers/permissions.php";
require_permission($pdo, 'plans.manage');
?>
<h1 class="text-2xl font-bold mb-5">Nouveau plan</h1>
<form action="../../../actions/subscriptions/plans/store.php" method="POST" class="bg-white p-5 rounded shadow max-w-2xl">
  <?= csrf_field() ?>
  <input name="code" placeholder="Code: basic" class="w-full border p-3 mb-3 rounded" required>
  <input name="name" placeholder="Nom du plan" class="w-full border p-3 mb-3 rounded" required>
  <div class="grid grid-cols-2 gap-3"><input type="number" step="0.01" name="price_monthly" placeholder="Prix mensuel" class="border p-3 mb-3 rounded" required><input type="number" step="0.01" name="price_yearly" placeholder="Prix annuel" class="border p-3 mb-3 rounded" required></div>
  <div class="grid grid-cols-2 gap-3"><input type="number" name="max_agents" placeholder="Max agents vide=illimité" class="border p-3 mb-3 rounded"><input type="number" name="max_agencies" placeholder="Max agences vide=illimité" class="border p-3 mb-3 rounded"></div>
  <textarea name="features" placeholder="Fonctionnalités" class="w-full border p-3 mb-3 rounded"></textarea>
  <button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require "../../../includes/footer.php"; ?>
