<?php
require "../../config/database.php";
require "../../includes/header.php";
require_once "../../app/Helpers/permissions.php";
require_permission($pdo, 'users.manage');
require "../../includes/sidebar.php";
require "../../includes/topbar.php";

$roleSlugs = assignable_role_slugs();
$in = implode(',', array_fill(0, count($roleSlugs), '?'));
$stmt = $pdo->prepare("SELECT * FROM roles WHERE slug IN ($in) ORDER BY FIELD(slug,'tenant_admin','admin','superviseur','agent'), name");
$stmt->execute($roleSlugs);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query("SELECT id, name FROM tenants WHERE status='active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<h1 class="text-2xl font-bold mb-5">Nouvel utilisateur</h1>

<div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded p-4 mb-5 max-w-2xl">
    Sécurité: aucun tenant ne peut attribuer le rôle <strong>super_admin</strong>. Ce rôle reste réservé au compte plateforme.
</div>

<form action="../../actions/users/store.php" method="post" class="bg-white rounded-xl shadow p-5 max-w-xl">
<?= csrf_field() ?>
<?php if (is_super_admin()): ?>
<select name="tenant_id" class="w-full border p-3 rounded mb-3" required>
    <option value="">Choisir tenant / banque</option>
    <?php foreach($tenants as $t): ?>
        <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option>
    <?php endforeach; ?>
</select>
<?php endif; ?>
<input name="name" class="w-full border p-3 rounded mb-3" placeholder="Nom complet" required>
<input name="username" class="w-full border p-3 rounded mb-3" placeholder="Identifiant" required>
<input name="password" type="password" minlength="8" class="w-full border p-3 rounded mb-3" placeholder="Mot de passe" required>
<select name="role" class="w-full border p-3 rounded mb-3">
<?php foreach($roleSlugs as $role): ?>
    <option value="<?= e($role) ?>"><?= e(ucfirst(str_replace('_',' ', $role))) ?></option>
<?php endforeach; ?>
</select>
<label class="block font-semibold mb-2">Rôles RBAC autorisés</label>
<div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
<?php foreach($roles as $r): ?>
<label class="border rounded p-2"><input type="checkbox" name="role_ids[]" value="<?= (int)$r['id'] ?>"> <?= e($r['name']) ?></label>
<?php endforeach; ?>
</div>
<button class="bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php require "../../includes/footer.php"; ?>
