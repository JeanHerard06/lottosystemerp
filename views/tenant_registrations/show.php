<?php
require "../../config/database.php"; require "../../includes/header.php"; require "../../includes/sidebar.php"; require "../../includes/topbar.php";
require_once "../../app/Helpers/tenant.php"; require_once "../../app/Helpers/security.php"; require_once "../../app/Helpers/csrf.php"; require_super_admin();
$id=(int)($_GET['id']??0); $stmt=$pdo->prepare("SELECT * FROM tenant_registrations WHERE id=?"); $stmt->execute([$id]); $r=$stmt->fetch(PDO::FETCH_ASSOC); if(!$r){die('Demande introuvable');}
?>
<h1 class="text-2xl font-bold mb-5">Demande: <?= e($r['business_name']) ?></h1>
<div class="bg-white p-5 rounded shadow max-w-3xl space-y-2 mb-5"><p><b>Responsable:</b> <?= e($r['owner_name']) ?></p><p><b>Email:</b> <?= e($r['email']) ?></p><p><b>Téléphone:</b> <?= e($r['phone']) ?></p><p><b>Plan:</b> <?= e($r['requested_plan']) ?></p><p><b>Adresse:</b> <?= e($r['address']) ?></p><p><b>Notes:</b> <?= nl2br(e($r['notes'])) ?></p><p><b>Status:</b> <?= e($r['status']) ?></p></div>
<?php if($r['status']==='pending'): ?>
<div class="grid md:grid-cols-2 gap-5 max-w-3xl">
<form action="../../actions/tenant_registrations/approve.php" method="POST" class="bg-white p-5 rounded shadow"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input name="admin_username" class="w-full border p-3 rounded mb-3" placeholder="Username admin tenant" value="<?= e(strtolower(preg_replace('/[^a-z0-9]+/','',$r['business_name']))) ?>" required><input name="admin_password" class="w-full border p-3 rounded mb-3" placeholder="Mot de passe temporaire" value="tenant123" required><input type="date" name="expires_at" class="w-full border p-3 rounded mb-3"><button class="bg-green-600 text-white px-5 py-3 rounded">Approuver</button></form>
<form action="../../actions/tenant_registrations/reject.php" method="POST" class="bg-white p-5 rounded shadow"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><textarea name="reason" class="w-full border p-3 rounded mb-3" placeholder="Raison rejet"></textarea><button class="bg-red-600 text-white px-5 py-3 rounded">Rejeter</button></form>
</div>
<?php endif; ?>
<?php require "../../includes/footer.php"; ?>
