-- Sprint 5: Risk Management
USE lotto_system;

ALTER TABLE limites
  ADD COLUMN game_type VARCHAR(30) NULL AFTER number_value,
  ADD COLUMN lottery_id INT NULL AFTER game_type,
  ADD COLUMN agency_id INT NULL AFTER lottery_id,
  ADD COLUMN threshold_percent DECIMAL(5,2) NOT NULL DEFAULT 80.00 AFTER max_amount,
  ADD INDEX idx_limites_scope (number_value, game_type, lottery_id, agency_id, status),
  ADD CONSTRAINT fk_limites_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_limites_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL;

ALTER TABLE blocages
  ADD COLUMN game_type VARCHAR(30) NULL AFTER number_value,
  ADD COLUMN lottery_id INT NULL AFTER game_type,
  ADD COLUMN agency_id INT NULL AFTER lottery_id,
  ADD COLUMN starts_at DATETIME NULL AFTER motif,
  ADD COLUMN ends_at DATETIME NULL AFTER starts_at,
  ADD INDEX idx_blocages_scope (number_value, game_type, lottery_id, agency_id, status),
  ADD CONSTRAINT fk_blocages_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_blocages_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL;

ALTER TABLE marriages
  ADD COLUMN game_type VARCHAR(30) NOT NULL DEFAULT 'mariage' AFTER number2,
  ADD COLUMN lottery_id INT NULL AFTER game_type,
  ADD COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active' AFTER payout,
  ADD CONSTRAINT fk_marriages_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL;

INSERT INTO permissions (name, slug, module)
SELECT 'Voir dashboard risque', 'risk.view', 'risk'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='risk.view');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.slug='admin' AND p.slug='risk.view'
  AND NOT EXISTS (SELECT 1 FROM role_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug='risk.view'
WHERE r.slug='superviseur'
  AND NOT EXISTS (SELECT 1 FROM role_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);
