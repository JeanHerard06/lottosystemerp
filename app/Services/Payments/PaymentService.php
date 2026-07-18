<?php

namespace App\Services\Payments;

require_once dirname(__DIR__) . '/TimeService.php';

use PDO;

class PaymentService
{
    public function __construct(private PDO $pdo) {}

    public function createAttempt(int $tenantId, ?int $invoiceId, string $gateway, float $amount, string $currency = 'USD', ?int $userId = null): string
    {
        $reference = 'PAY-' . \TimeService::now()->format('YmdHis') . '-' . bin2hex(random_bytes(4));
        $stmt = $this->pdo->prepare("INSERT INTO payment_attempts
            (tenant_id, invoice_id, gateway_code, reference, amount, currency, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([$tenantId, $invoiceId, $gateway, $reference, $amount, $currency, $userId]);
        return $reference;
    }

    public function markPaid(string $reference, ?string $externalReference = null): void
    {
        $now = \TimeService::sqlNow();
        $stmt = $this->pdo->prepare("UPDATE payment_attempts
            SET status='paid', external_reference=COALESCE(?, external_reference), paid_at=?, updated_at=?
            WHERE reference=? AND status IN ('pending','processing')");
        $stmt->execute([$externalReference, $now, $now, $reference]);
    }

    public function markFailed(string $reference, string $reason): void
    {
        $stmt = $this->pdo->prepare("UPDATE payment_attempts
            SET status='failed', failed_reason=?, updated_at=?
            WHERE reference=?");
        $stmt->execute([$reason, \TimeService::sqlNow(), $reference]);
    }
}
