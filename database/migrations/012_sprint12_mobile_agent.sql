-- Sprint 12 - Mobile Agent / Flutter API

ALTER TABLE users ADD COLUMN IF NOT EXISTS mobile_token VARCHAR(120) NULL AFTER api_token;
ALTER TABLE fiches ADD COLUMN IF NOT EXISTS sync_source ENUM('web','mobile') NOT NULL DEFAULT 'web' AFTER status;
ALTER TABLE fiches ADD COLUMN IF NOT EXISTS device_id VARCHAR(120) NULL AFTER sync_source;

CREATE INDEX IF NOT EXISTS idx_users_mobile_token ON users(mobile_token);
CREATE INDEX IF NOT EXISTS idx_fiches_sync_source ON fiches(sync_source);
CREATE INDEX IF NOT EXISTS idx_fiches_device_id ON fiches(device_id);

INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Utiliser API mobile', 'mobile.api.use', 'mobile'),
('Créer fiche mobile', 'mobile.fiches.create', 'mobile'),
('Voir dashboard mobile', 'mobile.dashboard.view', 'mobile');
