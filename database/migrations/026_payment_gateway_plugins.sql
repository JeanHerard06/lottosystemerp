CREATE TABLE IF NOT EXISTS payment_gateways (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  provider VARCHAR(100) NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  is_system TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tenant_payment_gateways (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  gateway_id INT NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  settings_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_tenant_gateway (tenant_id, gateway_id),
  INDEX idx_tpg_tenant (tenant_id),
  INDEX idx_tpg_gateway (gateway_id)
);

CREATE TABLE IF NOT EXISTS payment_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  invoice_id INT NULL,
  gateway_code VARCHAR(50) NOT NULL,
  reference VARCHAR(120) NOT NULL UNIQUE,
  external_reference VARCHAR(160) NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'USD',
  status ENUM('pending','processing','paid','failed','cancelled','refunded') DEFAULT 'pending',
  request_payload JSON NULL,
  response_payload JSON NULL,
  paid_at DATETIME NULL,
  failed_reason TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  INDEX idx_payment_tenant_status (tenant_id, status),
  INDEX idx_payment_invoice (invoice_id),
  INDEX idx_payment_gateway (gateway_code)
);

CREATE TABLE IF NOT EXISTS payment_webhook_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  gateway_code VARCHAR(50) NOT NULL,
  reference VARCHAR(160) NULL,
  event_type VARCHAR(120) NULL,
  payload JSON NULL,
  signature_valid TINYINT DEFAULT 0,
  processed TINYINT DEFAULT 0,
  error_message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_webhook_gateway_ref (gateway_code, reference),
  INDEX idx_webhook_processed (processed)
);

CREATE TABLE IF NOT EXISTS payment_refunds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  payment_attempt_id INT NOT NULL,
  reference VARCHAR(120) NOT NULL UNIQUE,
  amount DECIMAL(12,2) NOT NULL,
  reason TEXT NULL,
  status ENUM('pending','approved','rejected','completed','failed') DEFAULT 'pending',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  INDEX idx_refund_tenant (tenant_id),
  INDEX idx_refund_payment (payment_attempt_id)
);

INSERT IGNORE INTO payment_gateways(code,name,provider,status,is_system) VALUES
('moncash','MonCash','Digicel','active',1),
('natcash','NatCash','Natcom','active',1),
('stripe','Stripe','Stripe','active',1),
('paypal','PayPal','PayPal','active',1),
('manual','Manual / Cash','Internal','active',1);
