<?php

class PluginRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM plugins ORDER BY category, name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enableForTenant(int $tenantId, int $pluginId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("\n            INSERT INTO tenant_plugins (tenant_id, plugin_id, status, enabled_by, enabled_at)\n            VALUES (?, ?, 'enabled', ?, NOW())\n            ON DUPLICATE KEY UPDATE status='enabled', enabled_by=VALUES(enabled_by), enabled_at=NOW(), disabled_at=NULL\n        ");
        return $stmt->execute([$tenantId, $pluginId, $userId]);
    }

    public function disableForTenant(int $tenantId, int $pluginId): bool
    {
        $stmt = $this->pdo->prepare("\n            UPDATE tenant_plugins\n            SET status='disabled', disabled_at=NOW()\n            WHERE tenant_id=? AND plugin_id=?\n        ");
        return $stmt->execute([$tenantId, $pluginId]);
    }

    public function isEnabled(int $tenantId, string $pluginCode): bool
    {
        $stmt = $this->pdo->prepare("\n            SELECT COUNT(*)\n            FROM tenant_plugins tp\n            JOIN plugins p ON p.id = tp.plugin_id\n            WHERE tp.tenant_id=? AND p.code=? AND tp.status='enabled'\n        ");
        $stmt->execute([$tenantId, $pluginCode]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
