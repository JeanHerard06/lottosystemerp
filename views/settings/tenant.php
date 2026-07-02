<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/settings.php';

require_permission($pdo, 'settings.manage');

$tenantId = current_tenant_id();
if (is_super_admin()) {
    $tenantId = isset($_GET['tenant_id']) && $_GET['tenant_id'] !== '' ? (int)$_GET['tenant_id'] : null;
}

$tenants = [];
if (is_super_admin()) {
    $tenants = $pdo->query("SELECT id, name, status FROM tenants ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}

$settings = $tenantId ? array_merge(current_branding($pdo), tenant_settings_map($pdo, $tenantId)) : current_branding($pdo);
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Paramètres tenant</h1>
        <p class="text-gray-500">Branding, ticket, devise et email SMTP.</p>
    </div>
</div>

<?php if (is_super_admin()): ?>
<form method="GET" class="bg-white p-4 rounded shadow mb-5 flex gap-3 items-end">
    <div class="flex-1">
        <label class="block text-sm text-gray-600 mb-1">Tenant</label>
        <select name="tenant_id" class="w-full border p-3 rounded" onchange="this.form.submit()">
            <option value="">Choisir un tenant</option>
            <?php foreach ($tenants as $t): ?>
                <option value="<?= (int)$t['id'] ?>" <?= (int)$tenantId === (int)$t['id'] ? 'selected' : '' ?>>
                    <?= e($t['name']) ?> — <?= e($t['status']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>
<?php endif; ?>

<?php if (!$tenantId): ?>
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">Sélectionnez un tenant pour modifier ses paramètres.</div>
<?php else: ?>
<form action="/actions/settings/tenant_update.php" method="POST" enctype="multipart/form-data" class="bg-white p-5 rounded shadow max-w-3xl">
    <?= csrf_field() ?>
    <input type="hidden" name="tenant_id" value="<?= (int)$tenantId ?>">

    <h2 class="font-bold text-lg mb-3">Identité</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Nom commercial</label>
            <input name="business_name" value="<?= e($settings['business_name'] ?? '') ?>" class="w-full border p-3 rounded" required>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Téléphone</label>
            <input name="business_phone" value="<?= e($settings['business_phone'] ?? '') ?>" class="w-full border p-3 rounded">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Adresse</label>
            <input name="business_address" value="<?= e($settings['business_address'] ?? '') ?>" class="w-full border p-3 rounded">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Logo</label>
            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="w-full border p-3 rounded">
            <?php if (!empty($settings['logo_path'])): ?>
                <p class="text-sm mt-2">Logo actuel: <span class="font-mono"><?= e($settings['logo_path']) ?></span></p>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Devise</label>
            <select name="currency" class="w-full border p-3 rounded">
                <?php foreach (['HTG','USD','EUR'] as $c): ?>
                    <option value="<?= $c ?>" <?= ($settings['currency'] ?? 'HTG') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <h2 class="font-bold text-lg mt-6 mb-3">Ticket</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Sous-titre ticket</label>
            <input name="ticket_subtitle" value="<?= e($settings['ticket_subtitle'] ?? '') ?>" class="w-full border p-3 rounded">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Fuseau horaire</label>
            <input name="timezone" value="<?= e($settings['timezone'] ?? 'America/Port-au-Prince') ?>" class="w-full border p-3 rounded">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Footer ticket</label>
            <textarea name="ticket_footer" class="w-full border p-3 rounded" rows="2"><?= e($settings['ticket_footer'] ?? '') ?></textarea>
        </div>
    </div>

    <h2 class="font-bold text-lg mt-6 mb-3">Couleurs UI</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Couleur principale</label>
            <input type="color" name="primary_color" value="<?= e($settings['primary_color'] ?? '#000000') ?>" class="w-full h-12 border p-1 rounded">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Couleur accent</label>
            <input type="color" name="accent_color" value="<?= e($settings['accent_color'] ?? '#f59e0b') ?>" class="w-full h-12 border p-1 rounded">
        </div>
    </div>

    <h2 class="font-bold text-lg mt-6 mb-3">SMTP</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>" placeholder="SMTP Host" class="w-full border p-3 rounded">
        <input name="smtp_port" value="<?= e($settings['smtp_port'] ?? '') ?>" placeholder="SMTP Port" class="w-full border p-3 rounded">
        <input name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>" placeholder="SMTP User" class="w-full border p-3 rounded">
        <input name="smtp_from_email" value="<?= e($settings['smtp_from_email'] ?? '') ?>" placeholder="From Email" class="w-full border p-3 rounded">
        <input name="smtp_from_name" value="<?= e($settings['smtp_from_name'] ?? '') ?>" placeholder="From Name" class="w-full border p-3 rounded">
        <input type="password" name="smtp_password" placeholder="Nouveau mot de passe SMTP (laisser vide si inchangé)" class="w-full border p-3 rounded">
    </div>

    <button class="mt-6 bg-black text-white px-5 py-3 rounded">Enregistrer</button>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
