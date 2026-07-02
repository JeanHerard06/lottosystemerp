-- Sprint Tenant Security + Tenant Register

ALTER TABLE users MODIFY role ENUM('super_admin','tenant_admin','admin','superviseur','agent') NOT NULL DEFAULT 'agent';
UPDATE users SET role='super_admin', tenant_id=NULL WHERE username='admin';

INSERT IGNORE INTO roles(name, slug) VALUES
('Super Admin','super_admin'),
('Tenant Admin','tenant_admin');

INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Gérer demandes tenant','tenant_registrations.manage','saas'),
('Voir audit sécurité tenant','tenant.security.view','saas');

INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.slug='super_admin';

INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN (
  'dashboard.view','users.manage','agents.view','agents.manage','agencies.manage','supervisors.manage',
  'fiches.create','fiches.view','fiches.cancel','tirages.manage','gains.view','gains.calculate','gains.pay',
  'controls.manage','risk.view','finances.view','finances.manage','commissions.manage','transactions.void','reports.view','settings.manage','api.use'
) WHERE r.slug='tenant_admin';

CREATE TABLE IF NOT EXISTS tenant_registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  business_name VARCHAR(150) NOT NULL,
  owner_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  address VARCHAR(255) NULL,
  requested_plan ENUM('basic','pro','enterprise') NOT NULL DEFAULT 'basic',
  notes TEXT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_username VARCHAR(80) NULL,
  approved_by INT NULL,
  approved_at DATETIME NULL,
  rejected_by INT NULL,
  rejected_at DATETIME NULL,
  rejection_reason TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tenant_registrations_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
  CONSTRAINT fk_tenant_registrations_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_tenant_registrations_rejected_by FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_tenant_registrations_status (status),
  INDEX idx_tenant_registrations_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE gains ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id;
UPDATE gains g JOIN fiche_details fd ON fd.id = g.fiche_detail_id JOIN fiches f ON f.id = fd.fiche_id SET g.tenant_id = f.tenant_id WHERE g.tenant_id IS NULL;

-- Strong tenant isolation indexes
CREATE INDEX idx_tirages_tenant_date ON tirages(tenant_id, draw_date);
CREATE INDEX idx_gains_tenant_status ON gains(tenant_id, status);
CREATE INDEX idx_agencies_tenant_status ON agencies(tenant_id, status);
CREATE INDEX idx_agent_transactions_tenant_date ON agent_transactions(tenant_id, created_at);
