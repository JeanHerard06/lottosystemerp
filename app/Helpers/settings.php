<?php
require_once __DIR__ . '/tenant.php';

function tenant_setting(PDO $pdo, string $key, ?int $tenantId = null, $default = null) {
    $tenantId = $tenantId ?? current_tenant_id();
    if (!$tenantId) { return $default; }
    $stmt = $pdo->prepare('SELECT setting_value FROM tenant_settings WHERE tenant_id=? AND setting_key=? LIMIT 1');
    $stmt->execute([(int)$tenantId, $key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : $value;
}

function tenant_settings_map(PDO $pdo, ?int $tenantId = null): array {
    $tenantId = $tenantId ?? current_tenant_id();
    if (!$tenantId) { return []; }
    $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM tenant_settings WHERE tenant_id=?');
    $stmt->execute([(int)$tenantId]);
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $out[$row['setting_key']] = $row['setting_value'];
    }
    return $out;
}

function save_tenant_setting(PDO $pdo, int $tenantId, string $key, ?string $value): void {
    $stmt = $pdo->prepare('INSERT INTO tenant_settings (tenant_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    $stmt->execute([$tenantId, $key, $value]);
}

function current_branding(PDO $pdo): array {
    $tenantId = current_tenant_id();
    $defaults = [
        'business_name' => 'MCS LOTTO',
        'ticket_subtitle' => 'Système de gestion bòlèt',
        'business_phone' => '509-XXXX-XXXX',
        'business_address' => '',
        'primary_color' => '#000000',
        'accent_color' => '#f59e0b',
        'ticket_footer' => 'Conservez ce reçu. Aucun paiement sans validation.',
        'logo_path' => '',
        'timezone' => 'America/Port-au-Prince',
        'currency' => 'HTG',
        'smtp_host' => '',
        'smtp_port' => '',
        'smtp_user' => '',
        'smtp_from_email' => '',
        'smtp_from_name' => '',
    ];
    if (!$tenantId) { return $defaults; }
    return array_merge($defaults, tenant_settings_map($pdo, $tenantId));
}
