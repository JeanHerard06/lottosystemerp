<?php
require_once __DIR__ . '/tenant.php';

function system_setting(PDO $pdo, string $key, $default = null) {
    $stmt = $pdo->prepare('SELECT setting_value FROM system_settings WHERE setting_key=? LIMIT 1');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : $value;
}

function save_system_setting(PDO $pdo, string $key, ?string $value): void {
    $stmt = $pdo->prepare('INSERT INTO system_settings(setting_key, setting_value) VALUES(?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}

function system_settings_map(PDO $pdo): array {
    $rows = $pdo->query('SELECT setting_key, setting_value FROM system_settings ORDER BY setting_key')->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $row) { $out[$row['setting_key']] = $row['setting_value']; }
    return $out;
}
