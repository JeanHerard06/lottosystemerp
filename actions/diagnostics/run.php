<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Core/Autoload.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';

require_permission($pdo, 'health.view');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Méthode non autorisée.');
}
verify_csrf();

try {
    $service = new SystemDiagnosticService($pdo, dirname(__DIR__, 2));
    $report = $service->run();
    $filename = $service->save($report);
    $_SESSION['last_diagnostic_report'] = $report;
    $_SESSION['flash_success'] = 'Diagnostic terminé: ' . $filename;
} catch (Throwable $e) {
    error_log('Diagnostic failed: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Le diagnostic a échoué.';
}

header('Location: /views/settings/diagnostics.php');
exit;
