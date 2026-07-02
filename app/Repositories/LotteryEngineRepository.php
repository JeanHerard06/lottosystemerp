<?php

class LotteryEngineRepository
{
    public function findOpenWindow(PDO $pdo, int $tenantId, int $lotteryId, ?int $gameId = null): ?array
    {
        $sql = "SELECT * FROM sales_windows WHERE tenant_id=? AND lottery_id=? AND status='open' AND NOW() BETWEEN opens_at AND closes_at";
        $params = [$tenantId, $lotteryId];
        if ($gameId !== null) {
            $sql .= " AND game_id=?";
            $params[] = $gameId;
        }
        $sql .= " ORDER BY closes_at ASC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
