<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../_core/auth.php';

$user = require_api_auth($pdo);
api_success([
    'id' => (int)$user['user_id'],
    'name' => $user['name'],
    'role' => $user['role'],
    'tenant_id' => isset($user['tenant_id']) ? (int)$user['tenant_id'] : null,
]);
