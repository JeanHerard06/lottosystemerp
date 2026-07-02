<?php
require_once __DIR__ . '/tenant.php';
require_once __DIR__ . '/permissions.php';

function require_super_admin_middleware(PDO $pdo): void
{
    require_auth();
    if (!is_super_admin()) { http_response_code(403); die('Accès refusé: super_admin requis.'); }
}

function require_tenant_middleware(PDO $pdo): void
{
    require_auth();
    require_tenant_active($pdo);
    if (!is_super_admin() && !current_tenant_id()) { http_response_code(403); die('Tenant requis.'); }
}

function require_permission_middleware(PDO $pdo, string $permission): void
{
    require_permission($pdo, $permission);
}
