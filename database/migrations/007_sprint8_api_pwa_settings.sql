ALTER TABLE users ADD COLUMN api_token VARCHAR(100) NULL AFTER password;
CREATE INDEX idx_users_api_token ON users(api_token);
INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Gérer paramètres', 'settings.manage', 'settings'),
('Utiliser API', 'api.use', 'api');
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('settings.manage','api.use') WHERE r.slug='admin';
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug = 'api.use' WHERE r.slug IN ('agent','superviseur');
