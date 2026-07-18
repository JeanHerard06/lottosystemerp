<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../../app/Helpers/audit.php';

$user = mobile_user($pdo);
$agent = mobile_agent($pdo, (int)$user['id']);
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { $payload = $_POST; }

$amount = (float)($payload['amount'] ?? 0);
$note = trim((string)($payload['note'] ?? ''));

if ($amount <= 0) {
    mobile_json(['success' => false, 'message' => 'Montant invalide'], 422);
}

// This endpoint records a mobile commission payout request as an audit-style pending notification.
// Final approval/payment remains in the web finance workflow.
try {
    if (function_exists('audit_log')) {
        audit_log($pdo, (int)$user['id'], 'MOBILE_COMMISSION_REQUEST', 'Agent #' . (int)$agent['id'] . ' demande paiement commission: ' . $amount . ' | ' . $note);
    }
} catch (Throwable $e) {}

mobile_json(['success' => true, 'message' => 'Demande envoyée au superviseur.']);
