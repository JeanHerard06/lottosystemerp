<?php

namespace Plugins\Payments\paypal;

use App\Contracts\PaymentGatewayInterface;

class PaypalGateway implements PaymentGatewayInterface
{
    public function createPayment(array $invoice): array
    {
        return [
            'success' => true,
            'gateway' => 'paypal',
            'reference' => $invoice['reference'] ?? null,
            'checkout_url' => null,
            'message' => 'Payment initialized for paypal. Implement provider API here.'
        ];
    }

    public function verifyPayment(string $reference): array
    {
        return [
            'success' => true,
            'reference' => $reference,
            'status' => 'pending',
            'message' => 'Verification stub for paypal.'
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
            'message' => 'Refund not implemented for paypal yet.'
        ];
    }
}
