<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../app/Helpers/game_engine.php';

$user = mobile_user($pdo);
$tenantId = (int)($user['tenant_id'] ?? 0);
$rows = game_engine_types($pdo, $tenantId, true);
$data = array_map(static function (array $row): array {
    return [
        'id' => (int)$row['id'],
        'code' => (string)$row['code'],
        'name' => (string)$row['name'],
        'description' => $row['description'],
        'min_digits' => (int)$row['min_digits'],
        'max_digits' => (int)$row['max_digits'],
        'validation_pattern' => $row['validation_pattern'],
        'input_hint' => $row['input_hint'],
        'matching_engine' => $row['matching_engine'],
        'allow_duplicate' => (bool)$row['allow_duplicate'],
    ];
}, $rows);
mobile_json(['success' => true, 'message' => '', 'data' => $data]);
