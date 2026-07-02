-- Mobile v2 Offline Sync + Printing

ALTER TABLE fiches
  ADD COLUMN local_uuid VARCHAR(120) NULL,
  ADD COLUMN sync_status ENUM('online','pending_sync','synced','failed') DEFAULT 'online',
  ADD COLUMN synced_at DATETIME NULL,
  ADD COLUMN device_id VARCHAR(120) NULL;

CREATE TABLE IF NOT EXISTS mobile_sync_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NULL,
  device_id VARCHAR(120) NULL,
  local_uuid VARCHAR(120) NULL,
  entity_type VARCHAR(50) NOT NULL,
  status ENUM('pending','success','failed') DEFAULT 'pending',
  message TEXT NULL,
  payload JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_mobile_sync_tenant_status (tenant_id, status),
  INDEX idx_mobile_sync_local_uuid (local_uuid)
);

CREATE TABLE IF NOT EXISTS mobile_devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  user_id INT NOT NULL,
  device_id VARCHAR(120) NOT NULL,
  device_name VARCHAR(150) NULL,
  platform VARCHAR(50) NULL,
  last_seen_at DATETIME NULL,
  status ENUM('active','blocked') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_mobile_device (tenant_id, user_id, device_id)
);
