<?php
// API v2 skeleton: submit ticket claim.
header('Content-Type: application/json');
require_once __DIR__ . '/../../../bootstrap.php';

$ticketId = (int)($_POST['ticket_id'] ?? 0);
if ($ticketId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'ticket_id required']);
    exit;
}

// TODO: authenticate API user, enforce tenant scope, prevent double payment, create claim, audit log.
echo json_encode([
    'success' => true,
    'message' => 'Claim endpoint skeleton ready',
    'ticket_id' => $ticketId
]);
