<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';
require_once '../../../app/Services/Payments/PaymentService.php';

use App\Services\Payments\PaymentService;

$tenantId = (int)($_POST['tenant_id'] ?? 0);
$invoiceId = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : null;
$gateway = $_POST['gateway'] ?? 'manual';
$amount = (float)($_POST['amount'] ?? 0);
$currency = $_POST['currency'] ?? 'USD';

if ($tenantId <= 0 || $amount <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'tenant_id and amount are required']);
    exit;
}

$service = new PaymentService($pdo);
$reference = $service->createAttempt($tenantId, $invoiceId, $gateway, $amount, $currency);

echo json_encode(['success' => true, 'reference' => $reference]);
