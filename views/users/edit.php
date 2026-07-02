<?php
require "../../config/database.php";
require "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'users.manage');
require "../../includes/sidebar.php";
require "../../includes/topbar.php";

$id=(int)($_GET['id']??0);
$user = assert_user_mutable($pdo, $id);

if ($user['role'] === 'super_admin' && !is_super_admin()) { die('Accès refusé.'); }

$roleSlugs = $user['role'] === 'super_admin' ? ['super_admin'] : assignable_role_slugs();
$in = implode(',', array_fill(0, count($roleSlugs), '?'));
$stmt = $pdo->prepare("SELECT * FROM roles WHERE slug IN ($in) ORDER BY FIELD(slug,'super_admin','tenant_admin','admin','superviseur','agent'), name");
$stmt->execute($roleSlugs);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt=$pdo->prepare("SELECT role_id FROM user_roles WHERE user_id=?");
$stmt->execute([$id]);
$assigned=array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC),'role_id'));

$tenants = [];
if (is_super_admin() && $user['role'] !== 'super_admin') {
    $tenants = $pdo->query("SELECT id, name FROM tenants WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<h1 class="text-2xl font-bold mb-5">Modifier utilisateur</h1>

<?php if($user['role'] !== 'super_admin'): ?>
<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded p-4 mb-5 max-w-2xl">
    Sécurité: le rôle <strong>super_admin</strong> n’est pas attribuable aux utilisateurs tenant.
</div>
<?php endif; ?>

<form action="../../actions/users/update.php" method="post" class="bg-white rounded-xl shadow p-5 max-w-xl">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
<?php if (is_super_admin() && $user['role'] !== 'super_admin'): ?>
<select name="tenant_id" class="w-full border p-3 rounded mb-3" required>
    <?php foreach($tenants as $t): ?>
        <option value="<?= (int)$t['id'] ?>" <?= (int)$user['tenant_id']===(int)$t['id']?'selected':'' ?>><?= e($t['name']) ?></option>
    <?php endforeach; ?>
</select>
<?php endif; ?>
<input name="name" value="<?= e($user['name']) ?>" class="w-full border p-3 rounded mb-3" required>
<input name="username" value="<?= e($user['username']) ?>" class="w-full border p-3 rounded mb-3" required>
<input name="password" type="password" minlength="8" class="w-full border p-3 rounded mb-3" placeholder="Nouveau mot de passe (optionnel)">
<select name="role" class="w-full border p-3 rounded mb-3" <?= $user['role']==='super_admin'?'disabled':'' ?>>
<?php foreach($roleSlugs as $role): ?>
    <option value="<?= e($role) ?>" <?= $user['role']===$role?'selected':'' ?>><?= e(ucfirst(str_replace('_',' ', $role))) ?></option>
<?php endforeach; ?>
</select>
<?php if($user['role']==='super_admin'): ?><input type="hidden" name="role" value="super_admin"><?php endif; ?>
<select name="status" class="w-full border p-3 rounded mb-3"><option value="1" <?= $user['status']?'selected':'' ?>>Actif</option><option value="0" <?= !$user['status']?'selected':'' ?>>Inactif</option></select>
<label class="block font-semibold mb-2">Rôles RBAC autorisés</label>
<div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
<?php foreach($roles as $r): ?>
<label class="border rounded p-2"><input type="checkbox" name="role_ids[]" value="<?= (int)$r['id'] ?>" <?= in_array((int)$r['id'],$assigned,true)?'checked':'' ?> <?= $user['role']==='super_admin'?'disabled':'' ?>> <?= e($r['name']) ?></label>
<?php endforeach; ?>
</div>
<button class="bg-black text-white px-5 py-3 rounded">Mettre à jour</button>
</form>
<?php require "../../includes/footer.php"; ?>
