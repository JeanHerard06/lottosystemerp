<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function createPayment(array $invoice): array;
    public function verifyPayment(string $reference): array;
    public function handleWebhook(array $payload, array $headers = []): array;
    public function refundPayment(string $reference, float $amount, string $reason = ''): array;
}
