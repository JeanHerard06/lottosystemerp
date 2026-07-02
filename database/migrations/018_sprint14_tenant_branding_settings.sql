-- Sprint 14: Tenant branding, ticket settings, SMTP settings

INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Paramètres tenant', 'tenant.settings.manage', 'settings'),
('Voir journal système', 'system.logs.view', 'system');

INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('tenant.settings.manage','system.logs.view')
WHERE r.slug IN ('super_admin','admin','tenant_admin');

-- Defaults are stored as key/value rows. Insert missing branding defaults for existing tenants.
INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'business_name', name FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'ticket_subtitle', 'Système de gestion bòlèt' FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'business_phone', '' FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'business_address', '' FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'currency', 'HTG' FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'timezone', 'America/Port-au-Prince' FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'primary_color', '#000000' FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'accent_color', '#f59e0b' FROM tenants;

INSERT IGNORE INTO tenant_settings(tenant_id, setting_key, setting_value)
SELECT id, 'ticket_footer', 'Conservez ce reçu. Aucun paiement sans validation.' FROM tenants;
