<?php

namespace Plugins\Payments\stripe;

use App\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function createPayment(array $invoice): array
    {
        return [
            'success' => true,
            'gateway' => 'stripe',
            'reference' => $invoice['reference'] ?? null,
            'checkout_url' => null,
            'message' => 'Payment initialized for stripe. Implement provider API here.'
        ];
    }

    public function verifyPayment(string $reference): array
    {
        return [
            'success' => true,
            'reference' => $reference,
            'status' => 'pending',
            'message' => 'Verification stub for stripe.'
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
            'message' => 'Refund not implemented for stripe yet.'
        ];
    }
}
