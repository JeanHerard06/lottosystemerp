<?php

declare(strict_types=1);

require_once __DIR__ . '/TimeService.php';

final class LotteryEngineService
{
    public function canSell(array $salesWindow): bool
    {
        if (($salesWindow['status'] ?? '') !== 'open') {
            return false;
        }
        $now = TimeService::now();
        $opensAt = TimeService::parse((string)$salesWindow['opens_at']);
        $closesAt = TimeService::parse((string)$salesWindow['closes_at']);
        return $now >= $opensAt && $now < $closesAt;
    }

    public function closeWindow(PDO $pdo, int $windowId, int $userId): void
    {
        $stmt = $pdo->prepare("UPDATE sales_windows SET status='closed', closed_by=?, closed_at=? WHERE id=? AND status='open'");
        $stmt->execute([$userId, TimeService::sqlNow(), $windowId]);
    }
}
