<?php

namespace App\Services\Payments;

use PDO;
use Exception;

class PaymentService
{
    public function __construct(private PDO $pdo) {}

    public function createAttempt(int $tenantId, ?int $invoiceId, string $gateway, float $amount, string $currency = 'USD', ?int $userId = null): string
    {
        $reference = 'PAY-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $stmt = $this->pdo->prepare("INSERT INTO payment_attempts
            (tenant_id, invoice_id, gateway_code, reference, amount, currency, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->execute([$tenantId, $invoiceId, $gateway, $reference, $amount, $currency, $userId]);
        return $reference;
    }

    public function markPaid(string $reference, ?string $externalReference = null): void
    {
        $stmt = $this->pdo->prepare("UPDATE payment_attempts
            SET status='paid', external_reference=COALESCE(?, external_reference), paid_at=NOW(), updated_at=NOW()
            WHERE reference=? AND status IN ('pending','processing')");
        $stmt->execute([$externalReference, $reference]);
    }

    public function markFailed(string $reference, string $reason): void
    {
        $stmt = $this->pdo->prepare("UPDATE payment_attempts
            SET status='failed', failed_reason=?, updated_at=NOW()
            WHERE reference=?");
        $stmt->execute([$reason, $reference]);
    }
}
