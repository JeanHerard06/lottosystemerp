<?php
function current_agent(PDO $pdo): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM agents WHERE user_id = ? LIMIT 1');
    $stmt->execute([current_user_id()]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    return $agent ?: null;
}

function current_tenant_safe(): ?int
{
    if (function_exists('tenant_value')) {
        return tenant_value();
    }
    return isset($_SESSION['tenant_id']) && $_SESSION['tenant_id'] !== '' ? (int)$_SESSION['tenant_id'] : null;
}

function tenant_filter_sql(string $alias = ''): array
{
    if (function_exists('is_super_admin') && is_super_admin()) {
        return ['', []];
    }
    $tenantId = current_tenant_safe();
    if (!$tenantId) {
        return [' AND 1=0 ', []];
    }
    $prefix = $alias ? $alias . '.' : '';
    return [" AND {$prefix}tenant_id = ? ", [$tenantId]];
}

function fiche_effective_tenant_id(PDO $pdo, array $fiche): ?int
{
    if (isset($fiche['tenant_id']) && $fiche['tenant_id'] !== null && $fiche['tenant_id'] !== '') {
        return (int)$fiche['tenant_id'];
    }

    if (!empty($fiche['agent_tenant_id'])) {
        return (int)$fiche['agent_tenant_id'];
    }

    if (!empty($fiche['agent_id'])) {
        $stmt = $pdo->prepare('SELECT tenant_id FROM agents WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$fiche['agent_id']]);
        $tenantId = $stmt->fetchColumn();
        return $tenantId !== false && $tenantId !== null && $tenantId !== '' ? (int)$tenantId : null;
    }

    return null;
}

function can_access_fiche(PDO $pdo, array $fiche): bool
{
    if (function_exists('is_super_admin') && is_super_admin()) {
        return true;
    }

    $tenantId = current_tenant_safe();
    $ficheTenantId = fiche_effective_tenant_id($pdo, $fiche);

    // Tenant isolation first: every non-super-admin must stay inside its tenant.
    if (!$tenantId || !$ficheTenantId || (int)$ficheTenantId !== (int)$tenantId) {
        return false;
    }

    $role = current_user_role();
    if (in_array($role, ['tenant_admin', 'admin'], true)) {
        return true;
    }

    if ($role === 'agent') {
        $agent = current_agent($pdo);
        return $agent && (int)$fiche['agent_id'] === (int)$agent['id'];
    }

    if ($role === 'superviseur') {
        $stmt = $pdo->prepare('SELECT agency_id FROM supervisors WHERE user_id = ? AND tenant_id = ? LIMIT 1');
        $stmt->execute([current_user_id(), $tenantId]);
        $agencyId = $stmt->fetchColumn();
        if (!$agencyId) {
            return false;
        }
        $stmt = $pdo->prepare('SELECT agency_id FROM agents WHERE id = ? AND tenant_id = ? LIMIT 1');
        $stmt->execute([(int)$fiche['agent_id'], $tenantId]);
        return (int)$stmt->fetchColumn() === (int)$agencyId;
    }

    return false;
}

function fiche_code(string $prefix = 'FCH'): string
{
    return $prefix . '-' . date('YmdHis') . '-' . random_int(1000, 9999);
}

function unique_fiche_code(PDO $pdo, string $prefix = 'FCH'): string
{
    do {
        $code = fiche_code($prefix);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM fiches WHERE fiche_code = ?');
        $stmt->execute([$code]);
    } while ((int)$stmt->fetchColumn() > 0);
    return $code;
}

function validate_lottery_scope(PDO $pdo, ?int $lotteryId, ?int $tenantId = null): void
{
    if (!$lotteryId) {
        return;
    }

    if (function_exists('validate_lottery_sale_open')) {
        validate_lottery_sale_open($pdo, $lotteryId, $tenantId);
        return;
    }

    $sql = 'SELECT COUNT(*) FROM lotteries WHERE id = ? AND status = 1';
    $params = [$lotteryId];

    if ($tenantId && !(function_exists('is_super_admin') && is_super_admin())) {
        $sql .= ' AND (tenant_id = ? OR tenant_id IS NULL)';
        $params[] = $tenantId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    if ((int)$stmt->fetchColumn() === 0) {
        throw new RuntimeException('Lotterie inactive, introuvable ou hors tenant.');
    }
}
