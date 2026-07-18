<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

if (!function_exists('current_user_id')) {
    function current_user_id(): ?int { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; }
}
if (!function_exists('current_user_role')) {
    function current_user_role(): string { return $_SESSION['role'] ?? 'guest'; }
}
if (!function_exists('require_auth')) {
    function require_auth(): void {
        if (empty($_SESSION['user_id'])) { header('Location: /views/login.php'); exit; }
    }
}

function current_tenant_id(): ?int
{
    return (isset($_SESSION['tenant_id']) && $_SESSION['tenant_id'] !== '') ? (int)$_SESSION['tenant_id'] : null;
}

function is_super_admin(): bool
{
    $role = current_user_role();
    // Backward compatible: old installs may use role=admin for the platform user.
    return $role === 'super_admin' || ($role === 'admin' && current_tenant_id() === null);
}

function require_super_admin(): void
{
    require_auth();
    if (!is_super_admin()) {
        http_response_code(403);
        die('Accès refusé: super_admin requis.');
    }
}

function tenant_scope_clause(string $alias = '', string $prefix = 'AND'): array
{
    if (is_super_admin()) { return ['', []]; }
    $tenantId = current_tenant_id();
    if (!$tenantId) { return [" {$prefix} 1=0 ", []]; }
    $col = ($alias ? $alias . '.' : '') . 'tenant_id';
    return [" {$prefix} {$col} = ? ", [$tenantId]];
}

function tenant_where(string $alias = ''): array
{
    return tenant_scope_clause($alias, 'AND');
}

function tenant_value(): ?int { return current_tenant_id(); }

function tenant_insert_id(): ?int
{
    return is_super_admin() ? (isset($_POST['tenant_id']) && $_POST['tenant_id'] !== '' ? (int)$_POST['tenant_id'] : current_tenant_id()) : current_tenant_id();
}

function require_tenant(): int
{
    require_auth();
    $tenantId = current_tenant_id();
    if (!$tenantId && !is_super_admin()) {
        http_response_code(403);
        die('Aucun tenant actif sur cette session.');
    }
    return (int)$tenantId;
}

function ensure_record_tenant(?array $record, string $label = 'ressource'): void
{
    if (!$record) { http_response_code(404); die(ucfirst($label) . ' introuvable.'); }
    if (is_super_admin()) { return; }
    $tenantId = current_tenant_id();
    if (!isset($record['tenant_id']) || (int)$record['tenant_id'] !== (int)$tenantId) {
        http_response_code(403);
        die('Accès refusé: cette ' . $label . ' ne dépend pas de votre tenant.');
    }
}

function tenant_is_active(PDO $pdo, ?int $tenantId): bool
{
    if (!$tenantId) { return true; }
    $stmt = $pdo->prepare("SELECT status, expires_at FROM tenants WHERE id=? LIMIT 1");
    $stmt->execute([$tenantId]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tenant || $tenant['status'] !== 'active') { return false; }
    if (!empty($tenant['expires_at']) && $tenant['expires_at'] < TimeService::today()) { return false; }
    return true;
}

function require_tenant_active(PDO $pdo): void
{
    if (is_super_admin()) { return; }
    if (!tenant_is_active($pdo, current_tenant_id())) {
        session_destroy();
        header('Location: /views/login.php?error=' . urlencode('Tenant suspendu ou abonnement expiré.'));
        exit;
    }
}


function tenant_required_insert_id(): int
{
    $tenantId = tenant_insert_id();
    if (!$tenantId) {
        http_response_code(422);
        die('Tenant requis pour cette opération.');
    }
    return (int)$tenantId;
}

function visible_agencies(PDO $pdo, bool $activeOnly = true): array
{
    $where = [];
    $params = [];
    if (!is_super_admin()) {
        $where[] = 'tenant_id = ?';
        $params[] = current_tenant_id();
    }
    if ($activeOnly) {
        $where[] = "status = 'active'";
    }
    $sql = 'SELECT id, tenant_id, code, name, status FROM agencies';
    if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
    $sql .= ' ORDER BY name';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function ensure_agency_scope(PDO $pdo, ?int $agencyId, bool $allowNull = false): ?array
{
    if (!$agencyId) {
        if ($allowNull) { return null; }
        http_response_code(422);
        die('Agence requise.');
    }
    $stmt = $pdo->prepare('SELECT * FROM agencies WHERE id=? LIMIT 1');
    $stmt->execute([$agencyId]);
    $agency = $stmt->fetch(PDO::FETCH_ASSOC);
    ensure_record_tenant($agency ?: null, 'agence');
    return $agency;
}


/**
 * Sprint 13 tenant visibility helpers.
 * Data visibility rules:
 * - super_admin: all tenants
 * - tenant_admin/admin: current tenant
 * - superviseur: current tenant + assigned agency when applicable
 * - agent: current tenant + own agent row when applicable
 */
function current_agent_record(PDO $pdo): ?array
{
    $uid = current_user_id();
    if (!$uid) { return null; }
    $stmt = $pdo->prepare('SELECT * FROM agents WHERE user_id=? LIMIT 1');
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function current_supervisor_record(PDO $pdo): ?array
{
    $uid = current_user_id();
    if (!$uid) { return null; }
    $stmt = $pdo->prepare('SELECT * FROM supervisors WHERE user_id=? LIMIT 1');
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function scoped_agency_id(PDO $pdo): ?int
{
    if (is_super_admin()) { return null; }
    $role = current_user_role();
    if ($role === 'superviseur') {
        $sup = current_supervisor_record($pdo);
        return !empty($sup['agency_id']) ? (int)$sup['agency_id'] : null;
    }
    if ($role === 'agent') {
        $agent = current_agent_record($pdo);
        return !empty($agent['agency_id']) ? (int)$agent['agency_id'] : null;
    }
    return null;
}

function scoped_agent_id(PDO $pdo): ?int
{
    if (is_super_admin()) { return null; }
    if (current_user_role() === 'agent') {
        $agent = current_agent_record($pdo);
        return $agent ? (int)$agent['id'] : 0;
    }
    return null;
}

function tenant_agent_scope_clause(PDO $pdo, string $fAlias = 'f', string $aAlias = 'a', string $prefix = 'WHERE'): array
{
    $clauses = [];
    $params = [];
    if (!is_super_admin()) {
        $clauses[] = $fAlias . '.tenant_id = ?';
        $params[] = current_tenant_id();
        $agentId = scoped_agent_id($pdo);
        if ($agentId !== null) {
            $clauses[] = $fAlias . '.agent_id = ?';
            $params[] = $agentId;
        } else {
            $agencyId = scoped_agency_id($pdo);
            if ($agencyId) {
                $clauses[] = $aAlias . '.agency_id = ?';
                $params[] = $agencyId;
            }
        }
    }
    if (!$clauses) { return ['', []]; }
    return [' ' . $prefix . ' ' . implode(' AND ', $clauses) . ' ', $params];
}

function ensure_agent_visibility(PDO $pdo, int $agentId): array
{
    $stmt = $pdo->prepare('SELECT * FROM agents WHERE id=? LIMIT 1');
    $stmt->execute([$agentId]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    ensure_record_tenant($agent ?: null, 'agent');
    if (!is_super_admin()) {
        if (current_user_role() === 'agent' && (int)$agent['user_id'] !== (int)current_user_id()) {
            http_response_code(403); die('Accès refusé: agent hors compte.');
        }
        $agencyId = scoped_agency_id($pdo);
        if ($agencyId && !empty($agent['agency_id']) && (int)$agent['agency_id'] !== (int)$agencyId) {
            http_response_code(403); die('Accès refusé: agent hors agence.');
        }
    }
    return $agent;
}

// Apply the authenticated tenant's business timezone for web requests.
if (isset($pdo) && $pdo instanceof PDO && !empty($_SESSION['tenant_id'])) {
    require_once __DIR__ . '/../Services/TimeService.php';
    TimeService::boot($pdo, (int)$_SESSION['tenant_id']);
}
