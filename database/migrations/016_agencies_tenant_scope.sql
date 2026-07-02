-- Sprint 16: agencies tenant scope hardening
-- Objectif: une agence appartient toujours à un tenant; les utilisateurs tenant ne voient que leurs agences.

UPDATE agencies SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE agents a JOIN agencies ag ON ag.id = a.agency_id SET a.tenant_id = ag.tenant_id WHERE a.agency_id IS NOT NULL AND (a.tenant_id IS NULL OR a.tenant_id <> ag.tenant_id);
UPDATE supervisors s JOIN agencies ag ON ag.id = s.agency_id SET s.tenant_id = ag.tenant_id WHERE s.agency_id IS NOT NULL AND (s.tenant_id IS NULL OR s.tenant_id <> ag.tenant_id);

-- On new installations database.sql already uses tenant+code unique.
-- On existing installations, drop legacy global unique index if it exists manually if your MySQL version does not support conditional DROP INDEX:
-- ALTER TABLE agencies DROP INDEX code;
-- Then add tenant-safe indexes:
-- ALTER TABLE agencies ADD UNIQUE KEY uq_agencies_tenant_code (tenant_id, code);
-- ALTER TABLE agencies ADD INDEX idx_agencies_tenant_status (tenant_id, status);
