-- Sprint 20 - Audit logs center
INSERT IGNORE INTO permissions (name, slug, module) VALUES
('Voir journal audit', 'logs.view', 'logs'),
('Gérer journal audit', 'logs.manage', 'logs');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('logs.view','logs.manage')
WHERE r.slug IN ('super_admin','admin','tenant_admin');

CREATE INDEX IF NOT EXISTS idx_audit_tenant_date ON audit_logs(tenant_id, created_at);
CREATE INDEX IF NOT EXISTS idx_audit_action_date ON audit_logs(action_type, created_at);
