<?php

class FinancialAccountRepository
{
    public function findByCode(PDO $pdo, int $tenantId, string $code): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM financial_accounts WHERE tenant_id = ? AND code = ? LIMIT 1');
        $stmt->execute([$tenantId, $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
