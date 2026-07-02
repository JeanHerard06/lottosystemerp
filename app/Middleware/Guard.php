<?php

require_once __DIR__ . '/../Helpers/permissions.php';
require_once __DIR__ . '/../Helpers/tenant.php';

class Guard
{
    public static function permission(PDO $pdo, string $permission): void
    {
        require_permission($pdo, $permission);
    }

    public static function superAdmin(): void
    {
        require_super_admin();
    }

    public static function tenantActive(PDO $pdo): void
    {
        require_auth();
        require_tenant_active($pdo);
    }

    public static function tenantRecord(?array $record, string $label = 'ressource'): void
    {
        ensure_record_tenant($record, $label);
    }
}
