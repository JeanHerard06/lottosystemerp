-- Sprint 13 - Tenant Security & Data Isolation
-- This migration is intentionally additive/idempotent for existing installs.

-- Protected platform permissions
INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Manage Super Admin', 'super_admin.manage', 'system'),
('System Settings', 'system.settings', 'system');

-- Ensure main dashboards/API/report permissions exist
INSERT IGNORE INTO permissions(name, slug, module) VALUES
('View dashboard', 'dashboard.view', 'dashboard'),
('Manage users', 'users.manage', 'administration'),
('Manage roles', 'roles.manage', 'administration'),
('Manage tenants', 'tenants.manage', 'saas'),
('Manage subscriptions', 'subscriptions.manage', 'saas'),
('Manage plans', 'plans.manage', 'saas'),
('View basic reports', 'reports.basic', 'reports'),
('View own balance', 'balance.view.self', 'finances'),
('View own fiches', 'fiches.view.self', 'fiches');

-- Preserve super_admin as a platform-only role. Tenants must never be assigned this role.
INSERT IGNORE INTO roles(name, slug) VALUES
('Super Admin', 'super_admin'),
('Tenant Admin', 'tenant_admin'),
('Administrateur', 'admin'),
('Superviseur', 'superviseur'),
('Agent', 'agent');

-- Give dashboard to all built-in roles when missing.
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug='dashboard.view'
WHERE r.slug IN ('super_admin','tenant_admin','admin','superviseur','agent');

-- Protect SaaS permissions: assign only to super_admin by default.
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('tenants.manage','subscriptions.manage','plans.manage','super_admin.manage','system.settings')
WHERE r.slug='super_admin';

-- Tenant defaults.
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('users.manage','agencies.manage','agents.manage','lotteries.manage','tirages.manage','reports.view')
WHERE r.slug IN ('tenant_admin','admin');

-- Superviseur defaults.
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('agents.view','fiches.view','fiches.create','reports.basic')
WHERE r.slug='superviseur';

-- Agent defaults.
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('fiches.create','fiches.view.self','balance.view.self')
WHERE r.slug='agent';

-- Helpful indexes for tenant-scoped dashboards and lists.
CREATE INDEX IF NOT EXISTS idx_fiches_tenant_agent_created ON fiches(tenant_id, agent_id, created_at);
CREATE INDEX IF NOT EXISTS idx_agents_tenant_agency ON agents(tenant_id, agency_id);
CREATE INDEX IF NOT EXISTS idx_users_tenant_role ON users(tenant_id, role);
CREATE INDEX IF NOT EXISTS idx_supervisors_user_tenant ON supervisors(user_id, tenant_id, agency_id);
