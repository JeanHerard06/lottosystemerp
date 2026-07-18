<?php
require_once __DIR__ . '/auth.php';
$user = mobile_user($pdo);
mobile_json([
    'success' => true,
    'message' => 'Heure tenant chargée.',
    'data' => TimeService::meta(),
]);
