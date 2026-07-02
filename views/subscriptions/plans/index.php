<?php
require "../../../config/database.php";
require "../../../includes/header.php";
require "../../../includes/sidebar.php";
require "../../../includes/topbar.php";
require_once "../../../app/Helpers/permissions.php";
require_permission($pdo, 'plans.manage');
$plans = $pdo->query("SELECT * FROM subscription_plans ORDER BY price_monthly ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between mb-5">
  <h1 class="text-2xl font-bold">Plans SaaS</h1>
  <a href="create.php" class="bg-yellow-500 text-white px-4 py-2 rounded">+ Nouveau plan</a>
</div>
<table class="w-full bg-white rounded shadow">
  <thead><tr class="bg-gray-200 text-left"><th class="p-3">Code</th><th class="p-3">Nom</th><th class="p-3">Mensuel</th><th class="p-3">Annuel</th><th class="p-3">Agents</th><th class="p-3">Agences</th><th class="p-3">Statut</th><th class="p-3">Action</th></tr></thead>
  <tbody>
  <?php foreach($plans as $p): ?>
    <tr class="border-b">
      <td class="p-3 font-semibold"><?= e($p['code']) ?></td><td class="p-3"><?= e($p['name']) ?></td>
      <td class="p-3">$<?= number_format((float)$p['price_monthly'],2) ?></td><td class="p-3">$<?= number_format((float)$p['price_yearly'],2) ?></td>
      <td class="p-3"><?= $p['max_agents'] ?: 'Illimité' ?></td><td class="p-3"><?= $p['max_agencies'] ?: 'Illimité' ?></td>
      <td class="p-3"><?= e($p['status']) ?></td><td class="p-3"><a class="text-blue-600" href="edit.php?id=<?= (int)$p['id'] ?>">Modifier</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require "../../../includes/footer.php"; ?>
