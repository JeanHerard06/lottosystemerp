<?php

require_once __DIR__ . '/../Core/Repository.php';
require_once __DIR__ . '/../Helpers/tenant.php';
require_once __DIR__ . '/../Helpers/fiches.php';

class FicheRepository extends Repository
{
    public function findForTicket(int $id): ?array
    {
        return $this->fetchOne("\n            SELECT\n                f.*,\n                COALESCE(f.tenant_id, a.tenant_id) AS effective_tenant_id,\n                a.tenant_id AS agent_tenant_id,\n                u.name AS agent_name,\n                l.name AS lottery_name\n            FROM fiches f\n            JOIN agents a ON a.id = f.agent_id\n            JOIN users u ON u.id = a.user_id\n            LEFT JOIN lotteries l ON l.id = f.lottery_id\n            WHERE f.id = ?\n            LIMIT 1\n        ", [$id]);
    }

    public function details(int $ficheId): array
    {
        return $this->fetchAllRows('SELECT * FROM fiche_details WHERE fiche_id = ? ORDER BY id', [$ficheId]);
    }

    public function backfillTenantIfMissing(array $fiche): array
    {
        if (empty($fiche['tenant_id']) && !empty($fiche['effective_tenant_id']) && !empty($fiche['id'])) {
            $stmt = $this->pdo->prepare('UPDATE fiches SET tenant_id = ? WHERE id = ? AND tenant_id IS NULL');
            $stmt->execute([(int)$fiche['effective_tenant_id'], (int)$fiche['id']]);
            $fiche['tenant_id'] = (int)$fiche['effective_tenant_id'];
        }
        return $fiche;
    }

    public function canAccess(?array $fiche): bool
    {
        return $fiche !== null && can_access_fiche($this->pdo, $fiche);
    }
}
