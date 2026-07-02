<?php

class LotteryEngineService
{
    public function canSell(array $salesWindow): bool
    {
        $now = new DateTimeImmutable('now');
        $opensAt = new DateTimeImmutable($salesWindow['opens_at']);
        $closesAt = new DateTimeImmutable($salesWindow['closes_at']);
        return ($salesWindow['status'] ?? '') === 'open' && $now >= $opensAt && $now < $closesAt;
    }

    public function closeWindow(PDO $pdo, int $windowId, int $userId): void
    {
        $stmt = $pdo->prepare("UPDATE sales_windows SET status='closed', closed_by=?, closed_at=NOW() WHERE id=? AND status='open'");
        $stmt->execute([$userId, $windowId]);
    }
}
