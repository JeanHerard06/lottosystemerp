<?php
function invoice_status(float $total, float $paid): string
{
    if ($paid <= 0) {
        return 'issued';
    }
    if ($paid >= $total) {
        return 'paid';
    }
    return 'partial';
}

function refresh_invoice_status(PDO $pdo, int $invoiceId): void
{
    $stmt = $pdo->prepare("SELECT total_amount, paid_amount FROM subscription_invoices WHERE id=?");
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$invoice) {
        return;
    }
    $status = invoice_status((float)$invoice['total_amount'], (float)$invoice['paid_amount']);
    $stmt = $pdo->prepare("UPDATE subscription_invoices SET status=? WHERE id=? AND status NOT IN ('void')");
    $stmt->execute([$status, $invoiceId]);
}

function tenant_access_is_valid(array $tenant): bool
{
    if (($tenant['status'] ?? '') !== 'active') {
        return false;
    }
    if (!empty($tenant['expires_at']) && strtotime($tenant['expires_at']) < strtotime(date('Y-m-d'))) {
        return false;
    }
    return true;
}
