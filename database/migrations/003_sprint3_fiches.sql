-- Sprint 3: Fiches / ventes / tickets / annulation
USE lotto_system;

INSERT IGNORE INTO permissions (name, slug, module) VALUES
('Annuler fiches', 'fiches.cancel', 'fiches');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug = 'fiches.cancel'
WHERE r.slug IN ('admin','superviseur');
