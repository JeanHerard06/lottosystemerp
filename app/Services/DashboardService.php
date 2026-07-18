<?php

declare(strict_types=1);

require_once __DIR__ . '/../Core/Service.php';
require_once __DIR__ . '/../Repositories/DashboardRepository.php';
require_once __DIR__ . '/../Helpers/tenant.php';
require_once __DIR__ . '/../Helpers/mobile_dashboard_metrics.php';
require_once __DIR__ . '/TimeService.php';

/**
 * Builds the role-aware dashboard read model.
 *
 * It contains no direct SQL. DashboardRepository owns reads, while the shared
 * Mobile financial engine remains the source of truth for Agent financial KPIs.
 */
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
        $role = (string)current_user_role();
        $tenantId = (int)(current_tenant_id() ?? 0);
        $agent = current_agent_record($this->pdo);
        $supervisor = current_supervisor_record($this->pdo);

        [$ficheScope, $ficheParams, $scopeLabel] = $this->ficheScope(
            $role,
            $tenantId,
            $agent,
            $supervisor
        );
        [$agentScope, $agentParams] = $this->agentScope(
            $role,
            $tenantId,
            $agent,
            $supervisor
        );

        $totalVentes = $this->repo->salesTotal($ficheScope, $ficheParams);
        $totalFiches = $this->repo->ficheCount($ficheScope, $ficheParams);
        $totalGains = $this->repo->gainTotal($ficheScope, $ficheParams);
        $totalAgents = $this->repo->agentCount($agentScope, $agentParams);
        $agentsActifs = $this->repo->activeAgentCount($agentScope, $agentParams);

        $userScope = '';
        $userParams = [];
        if (!is_super_admin()) {
            $userScope = "u.tenant_id = ? AND u.role <> 'super_admin'";
            $userParams[] = $tenantId;
        }
        $usersCount = $this->repo->userCount($userScope, $userParams);

        $tenantsCount = 0;
        $tenantActive = 0;
        $subscriptionsExpiring = 0;
        if (is_super_admin()) {
            $tenantsCount = $this->repo->tenantCount();
            $tenantActive = $this->repo->activeTenantCount();
            $today = TimeService::today();
            $limitDate = TimeService::now()->modify('+15 days')->format('Y-m-d');
            $subscriptionsExpiring = $this->repo->subscriptionsExpiring($today, $limitDate);
        }

        $myBalance = 0.0;
        $myCommission = 0.0;
        $agentMetrics = null;
        if ($role === 'agent' && is_array($agent)) {
            $webUser = [
                'id' => (int)(current_user_id() ?? 0),
                'tenant_id' => $tenantId,
                'role' => $role,
            ];

            // This is deliberately the same engine used by Mobile Agent.
            $agentMetrics = mobile_agent_dashboard_metrics($this->pdo, $webUser, $agent);
            $myBalance = (float)$agentMetrics['amount_to_remit'];
            $myCommission = (float)$agentMetrics['today_commission'];
            $totalVentes = (float)$agentMetrics['today_sales'];
            $totalFiches = (int)$agentMetrics['today_fiches'];
            $totalGains = (float)$agentMetrics['today_gains'];
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
                'balance' => $agentMetrics !== null
                    ? (float)$agentMetrics['amount_to_remit']
                    : ($totalVentes - $totalGains),
                'agents' => $totalAgents,
                'agents_actifs' => $agentsActifs,
                'users' => $usersCount,
                'tenants' => $tenantsCount,
                'tenants_active' => $tenantActive,
                'subscriptions_expiring' => $subscriptionsExpiring,
                'my_balance' => $myBalance,
                'my_commission' => $myCommission,
                'agent_metrics' => $agentMetrics,
            ],
            'topAgents' => $this->repo->topAgents($agentScope, $agentParams),
            'lastFiches' => $this->repo->lastFiches($ficheScope, $ficheParams),
        ];
    }

    private function ficheScope(
        string $role,
        int $tenantId,
        ?array $agent,
        ?array $supervisor
    ): array {
        if (is_super_admin()) {
            return ['', [], 'Vue globale plateforme: tous les tenants.'];
        }

        $scope = ['f.tenant_id = ?'];
        $params = [$tenantId];
        $label = 'Vue limitée à votre tenant uniquement.';

        if ($role === 'agent') {
            $scope[] = 'f.agent_id = ?';
            $params[] = (int)($agent['id'] ?? 0);
            $label = 'Vue limitée à vos propres fiches, ventes et gains.';
        } elseif ($role === 'superviseur' && !empty($supervisor['agency_id'])) {
            $scope[] = 'a.agency_id = ?';
            $params[] = (int)$supervisor['agency_id'];
            $label = 'Vue limitée à votre agence.';
        }

        return [implode(' AND ', $scope), $params, $label];
    }

    private function agentScope(
        string $role,
        int $tenantId,
        ?array $agent,
        ?array $supervisor
    ): array {
        if (is_super_admin()) {
            return ['', []];
        }

        $scope = ['a.tenant_id = ?'];
        $params = [$tenantId];

        if ($role === 'agent') {
            $scope[] = 'a.id = ?';
            $params[] = (int)($agent['id'] ?? 0);
        } elseif ($role === 'superviseur' && !empty($supervisor['agency_id'])) {
            $scope[] = 'a.agency_id = ?';
            $params[] = (int)$supervisor['agency_id'];
        }

        return [implode(' AND ', $scope), $params];
    }
}
