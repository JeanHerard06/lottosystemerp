<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/system_settings.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';

require_permission($pdo, 'system.settings');
require_post();
csrf_verify();

$allowed = [
    'system.name',
    'system.timezone',
    'security.session_timeout_minutes',
    'security.max_login_attempts',
    'lottery.default_close_before_minutes',
    'lottery.auto_close_enabled',
    'finance.cash_difference_tolerance',
    'ticket.default_width_mm',
];

foreach (($_POST['settings'] ?? []) as $key => $value) {
    if (!in_array($key, $allowed, true)) { continue; }
    save_system_setting($pdo, $key, trim((string)$value));
}

if (function_exists('saveLog')) {
    saveLog($pdo, current_user_id(), 'SYSTEM_SETTINGS_UPDATE', 'Paramètres système modifiés');
}

header('Location: /views/settings/system.php?success=1');
exit;
