-- Sprint: Lotteries CRUD + tenant security

INSERT INTO permissions (name, slug, module)
SELECT 'Gérer lotteries', 'lotteries.manage', 'lotteries'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='lotteries.manage');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug='lotteries.manage'
WHERE r.slug IN ('super_admin','tenant_admin','superviseur')
  AND NOT EXISTS (
      SELECT 1 FROM role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

ALTER TABLE lotteries
  ADD INDEX idx_lotteries_tenant_status (tenant_id, status);
