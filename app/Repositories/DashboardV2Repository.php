<?php

class DashboardV2Repository
{
    public function __construct(private PDO $pdo) {}

    public function tenantSalesToday(?int $tenantId): float
    {
        $sql = "SELECT COALESCE(SUM(total_amount),0) FROM fiches WHERE DATE(created_at)=CURDATE()";
        $params = [];
        if ($tenantId !== null) {
            $sql .= " AND tenant_id=?";
            $params[] = $tenantId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    public function tenantGainsToday(?int $tenantId): float
    {
        $sql = "SELECT COALESCE(SUM(amount_won),0) FROM gains WHERE DATE(created_at)=CURDATE()";
        $params = [];
        if ($tenantId !== null) {
            $sql .= " AND tenant_id=?";
            $params[] = $tenantId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }

    public function openCashSessions(?int $tenantId): int
    {
        $sql = "SELECT COUNT(*) FROM cash_sessions WHERE status='open'";
        $params = [];
        if ($tenantId !== null) {
            $sql .= " AND tenant_id=?";
            $params[] = $tenantId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function openLotteries(?int $tenantId): int
    {
        $sql = "SELECT COUNT(*) FROM lotteries WHERE sales_status='open'";
        $params = [];
        if ($tenantId !== null) {
            $sql .= " AND tenant_id=?";
            $params[] = $tenantId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
