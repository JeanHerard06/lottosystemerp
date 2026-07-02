-- Sprint X: Full Tenant Security Audit Fix
-- Fixes missing tenant_id on gains and backfills data from related fiches.

ALTER TABLE gains ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;

UPDATE gains g
JOIN fiche_details fd ON fd.id = g.fiche_detail_id
JOIN fiches f ON f.id = fd.fiche_id
SET g.tenant_id = f.tenant_id
WHERE g.tenant_id IS NULL;

-- Optional indexes. If your MySQL version does not support IF NOT EXISTS for indexes,
-- ignore duplicate index errors if the index already exists.
CREATE INDEX IF NOT EXISTS idx_gains_tenant_status ON gains(tenant_id, status);
CREATE INDEX IF NOT EXISTS idx_gains_tirage_tenant ON gains(tirage_id, tenant_id);
CREATE INDEX IF NOT EXISTS idx_fiche_details_fiche ON fiche_details(fiche_id);
