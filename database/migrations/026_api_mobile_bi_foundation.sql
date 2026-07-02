-- Migration 026: API v2, Mobile Sync, BI foundation

CREATE TABLE IF NOT EXISTS api_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  device_name VARCHAR(150) NULL,
  ip_address VARCHAR(60) NULL,
  expires_at DATETIME NULL,
  revoked_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_api_tokens_user (user_id),
  INDEX idx_api_tokens_tenant (tenant_id)
);

CREATE TABLE IF NOT EXISTS api_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NULL,
  endpoint VARCHAR(255) NOT NULL,
  method VARCHAR(10) NOT NULL,
  status_code INT NULL,
  ip_address VARCHAR(60) NULL,
  user_agent TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_api_logs_tenant_date (tenant_id, created_at)
);

CREATE TABLE IF NOT EXISTS mobile_sync_queue (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  user_id INT NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id INT NULL,
  payload JSON NULL,
  status ENUM('pending','synced','failed') DEFAULT 'pending',
  attempts INT DEFAULT 0,
  last_error TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  synced_at DATETIME NULL,
  INDEX idx_mobile_sync_tenant_status (tenant_id, status)
);

CREATE TABLE IF NOT EXISTS analytics_snapshots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  snapshot_date DATE NOT NULL,
  metric_key VARCHAR(100) NOT NULL,
  metric_value DECIMAL(18,2) DEFAULT 0,
  meta JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_snapshot (tenant_id, snapshot_date, metric_key),
  INDEX idx_snapshot_tenant_date (tenant_id, snapshot_date)
);
