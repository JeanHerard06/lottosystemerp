<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$gateway = $_GET['gateway'] ?? 'unknown';
$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$reference = $payload['reference'] ?? $payload['transaction_id'] ?? null;

$stmt = $pdo->prepare("INSERT INTO payment_webhook_logs(gateway_code, reference, event_type, payload, signature_valid, processed)
VALUES (?, ?, ?, ?, 0, 0)");
$stmt->execute([$gateway, $reference, $payload['event'] ?? null, json_encode($payload)]);

echo json_encode(['success' => true, 'message' => 'Webhook received']);
