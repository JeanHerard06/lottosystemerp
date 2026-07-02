-- Sprint 16 - Operations & Cash Sessions

CREATE TABLE IF NOT EXISTS cash_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  agency_id INT NULL,
  agent_id INT NOT NULL,
  opened_by INT NULL,
  closed_by INT NULL,
  approved_by INT NULL,
  opening_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  closing_amount DECIMAL(12,2) NULL,
  expected_amount DECIMAL(12,2) NULL,
  difference_amount DECIMAL(12,2) NULL,
  status ENUM('open','closed','approved','rejected') NOT NULL DEFAULT 'open',
  notes TEXT NULL,
  opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  closed_at DATETIME NULL,
  approved_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cash_sessions_tenant_status (tenant_id, status),
  INDEX idx_cash_sessions_agent_status (agent_id, status),
  CONSTRAINT fk_cash_sessions_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_cash_sessions_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL,
  CONSTRAINT fk_cash_sessions_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
  CONSTRAINT fk_cash_sessions_opened_by FOREIGN KEY (opened_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_cash_sessions_closed_by FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_cash_sessions_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE fiches ADD COLUMN cash_session_id INT NULL AFTER lottery_id;
ALTER TABLE agent_transactions ADD COLUMN cash_session_id INT NULL AFTER agent_id;
ALTER TABLE agent_transactions ADD INDEX idx_agent_transactions_cash_session (cash_session_id);
ALTER TABLE agent_transactions ADD CONSTRAINT fk_agent_transactions_cash_session FOREIGN KEY (cash_session_id) REFERENCES cash_sessions(id) ON DELETE SET NULL;
ALTER TABLE fiches ADD INDEX idx_fiches_cash_session (cash_session_id);
ALTER TABLE fiches ADD CONSTRAINT fk_fiches_cash_session FOREIGN KEY (cash_session_id) REFERENCES cash_sessions(id) ON DELETE SET NULL;

INSERT INTO permissions (name, slug, module)
SELECT 'Gérer sessions caisse', 'cash_sessions.manage', 'cash_sessions'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='cash_sessions.manage');

INSERT INTO permissions (name, slug, module)
SELECT 'Approuver sessions caisse', 'cash_sessions.approve', 'cash_sessions'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='cash_sessions.approve');

INSERT INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('cash_sessions.manage','cash_sessions.approve')
WHERE r.slug IN ('admin','tenant_admin','super_admin')
AND NOT EXISTS (SELECT 1 FROM role_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);

INSERT INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug='cash_sessions.manage'
WHERE r.slug IN ('agent','superviseur')
AND NOT EXISTS (SELECT 1 FROM role_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);
