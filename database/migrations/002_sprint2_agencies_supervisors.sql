USE lotto_system;

INSERT IGNORE INTO permissions (name, slug, module) VALUES
('Gérer agences', 'agencies.manage', 'agencies'),
('Gérer superviseurs', 'supervisors.manage', 'supervisors');

INSERT IGNORE INTO agencies (code, name, status) VALUES
('PV', 'Petion-Ville', 'active'),
('DEL', 'Delmas', 'active'),
('CAP', 'Cap-Haïtien', 'active'),
('GON', 'Gonaïves', 'active');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug='admin' AND p.slug IN ('agencies.manage','supervisors.manage');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug='agents.manage'
WHERE r.slug='superviseur';
