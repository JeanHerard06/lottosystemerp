-- Sprint 18 - Notifications & workflow
USE lotto_system;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NULL,
  title VARCHAR(180) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  link_url VARCHAR(255) NULL,
  read_at DATETIME NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notifications_tenant_user_read (tenant_id, user_id, read_at),
  INDEX idx_notifications_created_at (created_at),
  CONSTRAINT fk_notifications_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_notifications_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO permissions (name, slug, module) VALUES
('Voir notifications', 'notifications.view', 'notifications'),
('Gérer notifications', 'notifications.manage', 'notifications');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('notifications.view','notifications.manage')
WHERE r.slug IN ('super_admin','tenant_admin','admin');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug='notifications.view'
WHERE r.slug IN ('superviseur','agent');
