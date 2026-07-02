-- Sprint 4: Tirages + calcul automatique des gains
-- Note: If a column already exists, skip the matching ALTER line manually.
USE lotto_system;

ALTER TABLE tirages
  ADD COLUMN status ENUM('open','processed','cancelled') NOT NULL DEFAULT 'open' AFTER draw_date,
  ADD COLUMN processed_at DATETIME NULL AFTER status;

ALTER TABLE gains
  ADD COLUMN is_paid TINYINT NOT NULL DEFAULT 0 AFTER status,
  ADD COLUMN paid_at DATETIME NULL AFTER is_paid,
  ADD COLUMN paid_by INT NULL AFTER paid_at;

ALTER TABLE gains
  ADD CONSTRAINT fk_gains_paid_by FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL;

INSERT IGNORE INTO permissions (name, slug, module) VALUES
('Calculer gains', 'gains.calculate', 'gains'),
('Payer gains', 'gains.pay', 'gains');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permissions p
WHERE r.slug='admin' AND p.slug IN ('gains.calculate','gains.pay');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permissions p
WHERE r.slug='superviseur' AND p.slug IN ('gains.calculate','gains.pay');
