<?php

namespace Plugins\Payments\manual;

use App\Contracts\PaymentGatewayInterface;

class ManualGateway implements PaymentGatewayInterface
{
    public function createPayment(array $invoice): array
    {
        return [
            'success' => true,
            'gateway' => 'manual',
            'reference' => $invoice['reference'] ?? null,
            'checkout_url' => null,
            'message' => 'Payment initialized for manual. Implement provider API here.'
        ];
    }

    public function verifyPayment(string $reference): array
    {
        return [
            'success' => true,
            'reference' => $reference,
            'status' => 'pending',
            'message' => 'Verification stub for manual.'
        ];
    }

    public function handleWebhook(array $payload, array $headers = []): array
    {
        return [
            'success' => true,
            'status' => 'received',
            'payload' => $payload
        ];
    }

    public function refundPayment(string $reference, float $amount, string $reason = ''): array
    {
        return [
            'success' => false,
            'reference' => $reference,
            'message' => 'Refund not implemented for manual yet.'
        ];
    }
}
