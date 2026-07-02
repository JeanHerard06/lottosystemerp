<?php

require_once __DIR__ . '/../Core/Repository.php';
require_once __DIR__ . '/../Helpers/tenant.php';

class DashboardRepository extends Repository
{
    public function scalarValue(string $sql, array $params = []): float
    {
        return (float)$this->scalar($sql, $params);
    }

    public function countValue(string $sql, array $params = []): int
    {
        return (int)$this->scalar($sql, $params);
    }

    public function topAgents(string $whereSql, array $params): array
    {
        $sql = "SELECT u.name, COALESCE(SUM(f.total_amount),0) ventes, COUNT(f.id) fiches\n                FROM agents a\n                JOIN users u ON u.id=a.user_id\n                LEFT JOIN fiches f ON f.agent_id=a.id";
        if ($whereSql !== '') {
            $sql .= ' WHERE ' . $whereSql;
        }
        $sql .= ' GROUP BY a.id, u.name ORDER BY ventes DESC LIMIT 5';
        return $this->fetchAllRows($sql, $params);
    }

    public function lastFiches(string $joinSql, string $whereSql, array $params): array
    {
        $sql = "SELECT f.fiche_code, f.total_amount, f.status, f.created_at, u.name AS agent_name " . $joinSql . ' ' . $whereSql . ' ORDER BY f.id DESC LIMIT 8';
        return $this->fetchAllRows($sql, $params);
    }
}
