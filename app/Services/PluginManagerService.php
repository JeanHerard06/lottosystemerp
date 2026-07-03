<?php

class PluginManagerService
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function listAvailablePlugins(): array
    {
        return $this->repository->all();
    }

    public function enableForTenant(int $tenantId, int $pluginId, int $userId): bool
    {
        return $this->repository->enableForTenant($tenantId, $pluginId, $userId);
    }

    public function disableForTenant(int $tenantId, int $pluginId): bool
    {
        return $this->repository->disableForTenant($tenantId, $pluginId);
    }

    public function isEnabled(int $tenantId, string $pluginCode): bool
    {
        return $this->repository->isEnabled($tenantId, $pluginCode);
    }
}
