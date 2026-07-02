<?php
require "../../../config/database.php";
require "../../../includes/header.php";
require "../../../includes/sidebar.php";
require "../../../includes/topbar.php";
require_once "../../../app/Helpers/permissions.php";
require_permission($pdo, 'plans.manage');
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT * FROM subscription_plans WHERE id=?"); $stmt->execute([$id]); $plan=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$plan){ die('Plan introuvable'); }
?>
<h1 class="text-2xl font-bold mb-5">Modifier plan</h1>
<form action="../../../actions/subscriptions/plans/update.php" method="POST" class="bg-white p-5 rounded shadow max-w-2xl">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$plan['id'] ?>">
  <input name="code" value="<?= e($plan['code']) ?>" class="w-full border p-3 mb-3 rounded" required>
  <input name="name" value="<?= e($plan['name']) ?>" class="w-full border p-3 mb-3 rounded" required>
  <div class="grid grid-cols-2 gap-3"><input type="number" step="0.01" name="price_monthly" value="<?= e($plan['price_monthly']) ?>" class="border p-3 mb-3 rounded" required><input type="number" step="0.01" name="price_yearly" value="<?= e($plan['price_yearly']) ?>" class="border p-3 mb-3 rounded" required></div>
  <div class="grid grid-cols-2 gap-3"><input type="number" name="max_agents" value="<?= e($plan['max_agents']) ?>" class="border p-3 mb-3 rounded"><input type="number" name="max_agencies" value="<?= e($plan['max_agencies']) ?>" class="border p-3 mb-3 rounded"></div>
  <textarea name="features" class="w-full border p-3 mb-3 rounded"><?= e($plan['features']) ?></textarea>
  <select name="status" class="w-full border p-3 mb-3 rounded"><option value="active" <?= $plan['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $plan['status']==='inactive'?'selected':'' ?>>inactive</option></select>
  <button class="bg-black text-white px-5 py-3 rounded">Mettre à jour</button>
</form>
<?php require "../../../includes/footer.php"; ?>
