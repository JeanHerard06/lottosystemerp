<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/topbar.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/system_settings.php';

require_permission($pdo, 'system.settings');

$settings = system_settings_map($pdo);
$fields = [
    'system.name' => ['Nom système', 'text'],
    'system.timezone' => ['Timezone', 'text'],
    'security.session_timeout_minutes' => ['Session timeout (minutes)', 'number'],
    'security.max_login_attempts' => ['Tentatives login max', 'number'],
    'lottery.default_close_before_minutes' => ['Fermeture par défaut avant tirage (minutes)', 'number'],
    'lottery.auto_close_enabled' => ['Auto close lotteries (1/0)', 'number'],
    'finance.cash_difference_tolerance' => ['Tolérance différence caisse', 'number'],
    'ticket.default_width_mm' => ['Largeur ticket par défaut (58/80)', 'number'],
];
?>
<div class="flex justify-between items-center mb-5">
    <div>
        <h1 class="text-2xl font-bold">Configuration système</h1>
        <p class="text-gray-500">Règles globales plateforme: sécurité, lottery, finance et ticket.</p>
    </div>
</div>

<form action="/actions/settings/system_update.php" method="POST" class="bg-white p-5 rounded shadow max-w-3xl">
    <?= csrf_field() ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($fields as $key => [$label, $type]): ?>
            <div>
                <label class="block text-sm text-gray-600 mb-1"><?= e($label) ?></label>
                <input type="<?= e($type) ?>" name="settings[<?= e($key) ?>]" value="<?= e($settings[$key] ?? '') ?>" class="form-control w-full border p-3 rounded">
                <p class="text-xs text-gray-400 mt-1"><?= e($key) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="btn bg-black text-white px-5 py-3 rounded mt-5">Enregistrer</button>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
