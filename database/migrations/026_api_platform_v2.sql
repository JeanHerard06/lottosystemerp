-- API Platform v2 migration scaffold

CREATE TABLE IF NOT EXISTS api_clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  name VARCHAR(150) NOT NULL,
  api_key VARCHAR(120) NOT NULL UNIQUE,
  api_secret_hash VARCHAR(255) NULL,
  status ENUM('active','inactive','revoked') DEFAULT 'active',
  rate_limit_per_minute INT DEFAULT 60,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS api_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NOT NULL,
  client_id INT NULL,
  token_hash VARCHAR(255) NOT NULL,
  refresh_token_hash VARCHAR(255) NULL,
  expires_at DATETIME NOT NULL,
  refresh_expires_at DATETIME NULL,
  revoked_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_api_tokens_user (user_id),
  INDEX idx_api_tokens_tenant (tenant_id)
);

CREATE TABLE IF NOT EXISTS api_request_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NULL,
  client_id INT NULL,
  method VARCHAR(10) NOT NULL,
  path VARCHAR(255) NOT NULL,
  status_code INT NULL,
  ip_address VARCHAR(80) NULL,
  duration_ms INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_api_logs_tenant_date (tenant_id, created_at),
  INDEX idx_api_logs_path (path)
);

CREATE TABLE IF NOT EXISTS webhook_endpoints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  url VARCHAR(500) NOT NULL,
  secret VARCHAR(255) NULL,
  events TEXT NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_webhook_tenant (tenant_id)
);

CREATE TABLE IF NOT EXISTS webhook_deliveries (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  endpoint_id INT NOT NULL,
  event_name VARCHAR(120) NOT NULL,
  payload MEDIUMTEXT NOT NULL,
  status ENUM('pending','delivered','failed') DEFAULT 'pending',
  attempts INT DEFAULT 0,
  last_error TEXT NULL,
  delivered_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_webhook_delivery_status (status),
  INDEX idx_webhook_delivery_tenant (tenant_id)
);
