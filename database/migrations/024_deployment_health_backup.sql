CREATE TABLE IF NOT EXISTS system_health_checks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  check_name VARCHAR(120) NOT NULL,
  status ENUM('ok','warning','critical') DEFAULT 'ok',
  message TEXT NULL,
  checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_health_status (status),
  INDEX idx_health_checked_at (checked_at)
);

CREATE TABLE IF NOT EXISTS system_backups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  backup_type ENUM('database','uploads','full','tenant') DEFAULT 'database',
  file_path VARCHAR(255) NOT NULL,
  file_size BIGINT DEFAULT 0,
  status ENUM('pending','success','failed') DEFAULT 'pending',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_backup_tenant (tenant_id),
  INDEX idx_backup_status (status),
  INDEX idx_backup_created_at (created_at)
);
