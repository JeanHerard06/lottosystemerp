<?php
require "../../config/database.php"; require "../../includes/header.php"; require "../../includes/sidebar.php"; require "../../includes/topbar.php";
require_once "../../app/Helpers/tenant.php"; require_once "../../app/Helpers/security.php"; require_super_admin();
$status = $_GET['status'] ?? 'pending';
$allowed=['pending','approved','rejected']; if(!in_array($status,$allowed,true)) $status='pending';
$stmt=$pdo->prepare("SELECT * FROM tenant_registrations WHERE status=? ORDER BY id DESC"); $stmt->execute([$status]); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="flex justify-between mb-5"><div><h1 class="text-2xl font-bold">Demandes tenant</h1><p class="text-gray-500">Validation super_admin obligatoire.</p></div><a class="bg-black text-white px-4 py-2 rounded" href="/views/tenants/index.php">Tenants</a></div>
<div class="mb-4 space-x-2"><a class="text-blue-600" href="?status=pending">Pending</a><a class="text-blue-600" href="?status=approved">Approved</a><a class="text-blue-600" href="?status=rejected">Rejected</a></div>
<table class="w-full bg-white rounded shadow"><thead><tr class="bg-gray-100 text-left"><th class="p-3">Business</th><th class="p-3">Responsable</th><th class="p-3">Email</th><th class="p-3">Plan</th><th class="p-3">Date</th><th class="p-3">Action</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr class="border-t"><td class="p-3 font-semibold"><?= e($r['business_name']) ?></td><td class="p-3"><?= e($r['owner_name']) ?></td><td class="p-3"><?= e($r['email']) ?></td><td class="p-3"><?= e($r['requested_plan']) ?></td><td class="p-3"><?= e($r['created_at']) ?></td><td class="p-3"><a class="text-blue-600" href="show.php?id=<?= (int)$r['id'] ?>">Ouvrir</a></td></tr><?php endforeach; ?>
</tbody></table><?php require "../../includes/footer.php"; ?>
