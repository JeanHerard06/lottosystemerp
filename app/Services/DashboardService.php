<?php

require_once __DIR__ . '/../Core/Service.php';
require_once __DIR__ . '/../Repositories/DashboardRepository.php';
require_once __DIR__ . '/../Helpers/tenant.php';

class DashboardService extends Service
{
    private DashboardRepository $repo;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->repo = new DashboardRepository($pdo);
    }

    public function build(): array
    {
        $role = current_user_role();
        $tenantId = current_tenant_id();
        $agent = current_agent_record($this->pdo);
        $supervisor = current_supervisor_record($this->pdo);

        $joinFiches = ' FROM fiches f JOIN agents a ON a.id=f.agent_id JOIN users u ON u.id=a.user_id ';
        $scope = [];
        $params = [];
        $scopeLabel = 'Vue globale plateforme: tous les tenants.';

        if (!is_super_admin()) {
            $scope[] = 'f.tenant_id = ?';
            $params[] = $tenantId;
            $scopeLabel = 'Vue limitée à votre tenant uniquement.';

            if ($role === 'agent') {
                $scope[] = 'f.agent_id = ?';
                $params[] = $agent ? (int)$agent['id'] : 0;
                $scopeLabel = 'Vue limitée à vos propres fiches, ventes et gains.';
            } elseif ($role === 'superviseur' && $supervisor && !empty($supervisor['agency_id'])) {
                $scope[] = 'a.agency_id = ?';
                $params[] = (int)$supervisor['agency_id'];
                $scopeLabel = 'Vue limitée à votre agence.';
            }
        }

        $where = $scope ? ' WHERE ' . implode(' AND ', $scope) : '';
        $totalVentes = $this->repo->scalarValue("SELECT COALESCE(SUM(f.total_amount),0) $joinFiches $where", $params);
        $totalFiches = $this->repo->countValue("SELECT COUNT(*) $joinFiches $where", $params);

        $gainSql = "SELECT COALESCE(SUM(g.amount_won),0)\n                    FROM gains g\n                    JOIN fiche_details fd ON fd.id=g.fiche_detail_id\n                    JOIN fiches f ON f.id=fd.fiche_id\n                    JOIN agents a ON a.id=f.agent_id";
        $gainWhere = $scope ? ' WHERE ' . implode(' AND ', $scope) . " AND g.status='won'" : " WHERE g.status='won'";
        $totalGains = $this->repo->scalarValue($gainSql . $gainWhere, $params);

        $agentWhere = [];
        $agentParams = [];
        if (!is_super_admin()) {
            $agentWhere[] = 'a.tenant_id = ?';
            $agentParams[] = $tenantId;
            if ($role === 'agent') {
                $agentWhere[] = 'a.id = ?';
                $agentParams[] = $agent ? (int)$agent['id'] : 0;
            } elseif ($role === 'superviseur' && $supervisor && !empty($supervisor['agency_id'])) {
                $agentWhere[] = 'a.agency_id = ?';
                $agentParams[] = (int)$supervisor['agency_id'];
            }
        }
        $agentWhereSql = $agentWhere ? ' WHERE ' . implode(' AND ', $agentWhere) : '';
        $totalAgents = $this->repo->countValue("SELECT COUNT(*) FROM agents a $agentWhereSql", $agentParams);
        $agentsActifs = $this->repo->countValue("SELECT COUNT(*) FROM agents a JOIN users u ON u.id=a.user_id $agentWhereSql" . ($agentWhere ? " AND u.status=1" : " WHERE u.status=1"), $agentParams);

        $userWhere = [];
        $userParams = [];
        if (!is_super_admin()) {
            $userWhere[] = "u.tenant_id = ? AND u.role <> 'super_admin'";
            $userParams[] = $tenantId;
        }
        $userWhereSql = $userWhere ? ' WHERE ' . implode(' AND ', $userWhere) : '';
        $usersCount = $this->repo->countValue("SELECT COUNT(*) FROM users u $userWhereSql", $userParams);

        $tenantsCount = $tenantActive = $subscriptionsExpiring = 0;
        if (is_super_admin()) {
            $tenantsCount = $this->repo->countValue('SELECT COUNT(*) FROM tenants');
            $tenantActive = $this->repo->countValue("SELECT COUNT(*) FROM tenants WHERE status='active'");
            try {
                $subscriptionsExpiring = $this->repo->countValue("SELECT COUNT(*) FROM tenant_subscriptions WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)");
            } catch (Throwable $e) {
                $subscriptionsExpiring = 0;
            }
        }

        $topWhere = [];
        $topParams = [];
        if (!is_super_admin()) {
            $topWhere[] = 'a.tenant_id = ?';
            $topParams[] = $tenantId;
            if ($role === 'agent') {
                $topWhere[] = 'a.id = ?';
                $topParams[] = $agent ? (int)$agent['id'] : 0;
            } elseif ($role === 'superviseur' && $supervisor && !empty($supervisor['agency_id'])) {
                $topWhere[] = 'a.agency_id = ?';
                $topParams[] = (int)$supervisor['agency_id'];
            }
        }

        $myBalance = 0.0;
        $myCommission = 0.0;
        if ($role === 'agent' && $agent) {
            $myBalance = (float)($agent['balance'] ?? 0);
            try {
                $myCommission = $this->repo->scalarValue("SELECT COALESCE(SUM(amount),0) FROM agent_transactions WHERE agent_id=? AND type='commission'", [(int)$agent['id']]);
            } catch (Throwable $e) {
                $myCommission = 0.0;
            }
        }

        return [
            'role' => $role,
            'agent' => $agent,
            'supervisor' => $supervisor,
            'scopeLabel' => $scopeLabel,
            'totals' => [
                'ventes' => $totalVentes,
                'fiches' => $totalFiches,
                'gains' => $totalGains,
                'balance' => $totalVentes - $totalGains,
                'agents' => $totalAgents,
                'agents_actifs' => $agentsActifs,
                'users' => $usersCount,
                'tenants' => $tenantsCount,
                'tenants_active' => $tenantActive,
                'subscriptions_expiring' => $subscriptionsExpiring,
                'my_balance' => $myBalance,
                'my_commission' => $myCommission,
            ],
            'topAgents' => $this->repo->topAgents(implode(' AND ', $topWhere), $topParams),
            'lastFiches' => $this->repo->lastFiches($joinFiches, $where, $params),
        ];
    }
}
