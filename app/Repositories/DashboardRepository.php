<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Repository.php';

/**
 * Central read model for every Web dashboard.
 *
 * All SQL used by DashboardService lives here so the service can focus on
 * permissions, role scope and presentation data only.
 */
class DashboardRepository extends Repository
{
    public function salesTotal(string $whereSql = '', array $params = []): float
    {
        return (float)$this->scalar(
            'SELECT COALESCE(SUM(f.total_amount), 0) '
            . $this->ficheJoin()
            . $this->where($whereSql),
            $params
        );
    }

    public function ficheCount(string $whereSql = '', array $params = []): int
    {
        return (int)$this->scalar(
            'SELECT COUNT(*) '
            . $this->ficheJoin()
            . $this->where($whereSql),
            $params
        );
    }

    public function gainTotal(string $whereSql = '', array $params = []): float
    {
        $conditions = trim($whereSql);
        $conditions = $conditions === ''
            ? "g.status = 'won'"
            : $conditions . " AND g.status = 'won'";

        return (float)$this->scalar(
            "SELECT COALESCE(SUM(g.amount_won), 0)
             FROM gains g
             JOIN fiche_details fd ON fd.id = g.fiche_detail_id
             JOIN fiches f ON f.id = fd.fiche_id
             JOIN agents a ON a.id = f.agent_id
             JOIN users u ON u.id = a.user_id"
            . $this->where($conditions),
            $params
        );
    }

    public function agentCount(string $whereSql = '', array $params = []): int
    {
        return (int)$this->scalar(
            'SELECT COUNT(*) FROM agents a' . $this->where($whereSql),
            $params
        );
    }

    public function activeAgentCount(string $whereSql = '', array $params = []): int
    {
        $conditions = trim($whereSql);
        $conditions = $conditions === '' ? 'u.status = 1' : $conditions . ' AND u.status = 1';

        return (int)$this->scalar(
            'SELECT COUNT(*) FROM agents a JOIN users u ON u.id = a.user_id'
            . $this->where($conditions),
            $params
        );
    }

    public function userCount(string $whereSql = '', array $params = []): int
    {
        return (int)$this->scalar(
            'SELECT COUNT(*) FROM users u' . $this->where($whereSql),
            $params
        );
    }

    public function tenantCount(): int
    {
        return (int)$this->scalar('SELECT COUNT(*) FROM tenants');
    }

    public function activeTenantCount(): int
    {
        return (int)$this->scalar("SELECT COUNT(*) FROM tenants WHERE status = 'active'");
    }

    public function subscriptionsExpiring(string $today, string $limitDate): int
    {
        try {
            return (int)$this->scalar(
                "SELECT COUNT(*)
                 FROM tenant_subscriptions
                 WHERE status = 'active'
                   AND end_date BETWEEN ? AND ?",
                [$today, $limitDate]
            );
        } catch (Throwable $e) {
            // Older installations may not have tenant_subscriptions yet.
            return 0;
        }
    }

    public function topAgents(string $whereSql = '', array $params = [], int $limit = 5): array
    {
        $limit = max(1, min(25, $limit));
        $sql = "SELECT a.id, u.name,
                       COALESCE(SUM(f.total_amount), 0) AS ventes,
                       COUNT(f.id) AS fiches
                FROM agents a
                JOIN users u ON u.id = a.user_id
                LEFT JOIN fiches f ON f.agent_id = a.id"
                . $this->where($whereSql)
                . " GROUP BY a.id, u.name
                    ORDER BY ventes DESC
                    LIMIT {$limit}";

        return $this->fetchAllRows($sql, $params);
    }

    public function lastFiches(string $whereSql = '', array $params = [], int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));
        $sql = "SELECT f.id, f.fiche_code, f.total_amount, f.status, f.created_at,
                       u.name AS agent_name
                " . $this->ficheJoin()
                . $this->where($whereSql)
                . " ORDER BY f.id DESC LIMIT {$limit}";

        return $this->fetchAllRows($sql, $params);
    }

    private function ficheJoin(): string
    {
        return ' FROM fiches f JOIN agents a ON a.id = f.agent_id JOIN users u ON u.id = a.user_id';
    }

    private function where(string $conditions): string
    {
        $conditions = trim($conditions);
        return $conditions === '' ? '' : ' WHERE ' . $conditions;
    }
}
