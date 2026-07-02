-- Sprint 10: SaaS Multi-Tenant foundation

CREATE TABLE IF NOT EXISTS tenants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  plan ENUM('basic','pro','enterprise') NOT NULL DEFAULT 'basic',
  status ENUM('active','suspended','cancelled') NOT NULL DEFAULT 'active',
  expires_at DATE NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tenant_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tenant_setting (tenant_id, setting_key),
  CONSTRAINT fk_tenant_settings_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tenant_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  plan ENUM('basic','pro','enterprise') NOT NULL DEFAULT 'basic',
  status ENUM('trial','active','past_due','cancelled') NOT NULL DEFAULT 'trial',
  starts_at DATE NULL,
  ends_at DATE NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_provider VARCHAR(50) NULL,
  reference_no VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tenant_subscriptions_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO tenants(id, name, slug, plan, status) VALUES (1, 'Default Bank', 'default', 'enterprise', 'active');

ALTER TABLE users ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE agencies ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE agents ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE supervisors ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE lotteries ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE tirages ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE fiches ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE gains ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE limites ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE blocages ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE marriages ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE commissions ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE agent_transactions ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE cash_accounts ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE cash_movements ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
ALTER TABLE audit_logs ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;

UPDATE users SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE agencies SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE agents SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE supervisors SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE lotteries SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE tirages SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE fiches SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE gains g JOIN fiche_details fd ON fd.id = g.fiche_detail_id JOIN fiches f ON f.id = fd.fiche_id SET g.tenant_id = f.tenant_id WHERE g.tenant_id IS NULL;
UPDATE limites SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE blocages SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE marriages SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE commissions SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE agent_transactions SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE cash_accounts SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE cash_movements SET tenant_id = 1 WHERE tenant_id IS NULL;
UPDATE audit_logs SET tenant_id = 1 WHERE tenant_id IS NULL;

INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Voir super admin', 'superadmin.view', 'saas'),
('Gérer tenants', 'tenants.manage', 'saas');

INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('superadmin.view','tenants.manage') WHERE r.slug='admin';

CREATE INDEX idx_users_tenant ON users(tenant_id);
CREATE INDEX idx_fiches_tenant_date ON fiches(tenant_id, created_at);
CREATE INDEX idx_agents_tenant ON agents(tenant_id);
