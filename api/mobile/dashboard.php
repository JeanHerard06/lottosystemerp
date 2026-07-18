<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../../app/Helpers/mobile_dashboard_metrics.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);

try {
    $metrics = mobile_agent_dashboard_metrics($pdo, $user, $agent);
    mobile_json([
        'success' => true,
        'message' => 'Dashboard agent chargé.',
        'data' => $metrics,
    ]);
} catch (Throwable $e) {
    mobile_json([
        'success' => false,
        'message' => 'Erreur calcul dashboard: ' . $e->getMessage(),
        'data' => null,
        'debug' => [
            'exception' => get_class($e),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ],
    ], 500);
}
