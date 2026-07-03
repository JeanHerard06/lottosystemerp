<?php

class DashboardV2Service
{
    public function __construct(private DashboardV2Repository $repo) {}

    public function tenantAdminMetrics(int $tenantId): array
    {
        $sales = $this->repo->tenantSalesToday($tenantId);
        $gains = $this->repo->tenantGainsToday($tenantId);
        return [
            'sales_today' => $sales,
            'gains_today' => $gains,
            'profit_today' => $sales - $gains,
            'open_cash_sessions' => $this->repo->openCashSessions($tenantId),
            'open_lotteries' => $this->repo->openLotteries($tenantId),
        ];
    }

    public function superAdminMetrics(): array
    {
        $sales = $this->repo->tenantSalesToday(null);
        $gains = $this->repo->tenantGainsToday(null);
        return [
            'global_sales_today' => $sales,
            'global_gains_today' => $gains,
            'global_profit_today' => $sales - $gains,
            'open_cash_sessions' => $this->repo->openCashSessions(null),
            'open_lotteries' => $this->repo->openLotteries(null),
        ];
    }
}
