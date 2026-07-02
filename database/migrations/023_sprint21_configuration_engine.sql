-- Sprint 21: Configuration ERP & Business Rules

CREATE TABLE IF NOT EXISTS system_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(150) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lottery_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  lottery_id INT NOT NULL,
  day_of_week TINYINT NULL COMMENT '0=Sunday, 1=Monday ... 6=Saturday, NULL=every day',
  draw_time TIME NOT NULL,
  close_before_minutes INT NOT NULL DEFAULT 10,
  sales_open_time TIME NULL,
  sales_close_time TIME NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lottery_schedules_tenant_lottery (tenant_id, lottery_id, status),
  INDEX idx_lottery_schedules_day_time (tenant_id, day_of_week, draw_time),
  CONSTRAINT fk_lottery_schedules_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_lottery_schedules_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cron_runs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_name VARCHAR(120) NOT NULL,
  status ENUM('success','failed','running') NOT NULL DEFAULT 'success',
  message TEXT NULL,
  started_at DATETIME NOT NULL,
  finished_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cron_runs_job_date (job_name, started_at),
  INDEX idx_cron_runs_status (status)
) ENGINE=InnoDB;

INSERT INTO system_settings(setting_key, setting_value) VALUES
('system.name','Lotto ERP Enterprise'),
('system.timezone','America/Port-au-Prince'),
('security.session_timeout_minutes','60'),
('security.max_login_attempts','5'),
('lottery.default_close_before_minutes','10'),
('lottery.auto_close_enabled','1'),
('finance.cash_difference_tolerance','0'),
('ticket.default_width_mm','80')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO permissions(name, slug, module)
SELECT 'Paramètres système', 'system.settings', 'settings'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='system.settings');
INSERT INTO permissions(name, slug, module)
SELECT 'Gérer horaires lotteries', 'lottery_schedules.manage', 'lotteries'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='lottery_schedules.manage');
INSERT INTO permissions(name, slug, module)
SELECT 'Voir santé système', 'health.view', 'system'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='health.view');
INSERT INTO permissions(name, slug, module)
SELECT 'Gérer cron jobs', 'cron.manage', 'system'
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE slug='cron.manage');

INSERT INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN ('system.settings','lottery_schedules.manage','health.view','cron.manage')
WHERE r.slug='super_admin'
  AND NOT EXISTS (SELECT 1 FROM role_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);

INSERT INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN ('lottery_schedules.manage')
WHERE r.slug IN ('tenant_admin','admin')
  AND NOT EXISTS (SELECT 1 FROM role_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);
