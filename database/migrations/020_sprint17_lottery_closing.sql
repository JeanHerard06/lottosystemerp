-- Sprint 17: Lottery Closing & Draw Cutoff

ALTER TABLE lotteries
  ADD COLUMN draw_time TIME NULL,
  ADD COLUMN close_before_minutes INT NOT NULL DEFAULT 10,
  ADD COLUMN sales_status ENUM('open','closed','drawn') NOT NULL DEFAULT 'open',
  ADD COLUMN auto_close_enabled TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN closed_at DATETIME NULL,
  ADD COLUMN closed_by INT NULL;

ALTER TABLE lotteries
  ADD INDEX idx_lotteries_sales_status (tenant_id, sales_status, draw_time),
  ADD INDEX idx_lotteries_closed_by (closed_by);

INSERT INTO permissions (name, slug, module)
SELECT 'Fermer / rouvrir lotteries', 'lotteries.close', 'lotteries'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='lotteries.close');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug='lotteries.close'
WHERE r.slug IN ('super_admin','tenant_admin','superviseur')
  AND NOT EXISTS (
      SELECT 1 FROM role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );
