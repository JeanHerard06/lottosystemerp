<?php

function plugin_enabled(PDO $pdo, int $tenantId, string $pluginCode): bool
{
    $stmt = $pdo->prepare("\n        SELECT COUNT(*)\n        FROM tenant_plugins tp\n        JOIN plugins p ON p.id = tp.plugin_id\n        WHERE tp.tenant_id = ?\n          AND p.code = ?\n          AND tp.status = 'enabled'\n    ");
    $stmt->execute([$tenantId, $pluginCode]);
    return (int)$stmt->fetchColumn() > 0;
}

function dispatch_plugin_event(PDO $pdo, ?int $tenantId, string $eventName, array $payload = [], ?int $pluginId = null): void
{
    $stmt = $pdo->prepare("\n        INSERT INTO plugin_events (tenant_id, plugin_id, event_name, payload, status)\n        VALUES (?, ?, ?, ?, 'pending')\n    ");
    $stmt->execute([
        $tenantId,
        $pluginId,
        $eventName,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ]);
}
