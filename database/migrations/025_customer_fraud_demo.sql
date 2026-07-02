-- Sprint RC1 Phase 6: Customer Portal + Fraud Detection foundations

CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(150) NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_customers_tenant (tenant_id),
  INDEX idx_customers_phone (phone)
);

CREATE TABLE IF NOT EXISTS ticket_verifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  fiche_id INT NULL,
  verification_code VARCHAR(120) NOT NULL,
  result ENUM('valid','invalid','cancelled','paid','not_found') NOT NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ticket_verifications_tenant (tenant_id),
  INDEX idx_ticket_verifications_code (verification_code)
);

CREATE TABLE IF NOT EXISTS fraud_alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NULL,
  alert_type VARCHAR(100) NOT NULL,
  severity ENUM('low','medium','high','critical') DEFAULT 'medium',
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  status ENUM('open','reviewing','resolved','dismissed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME NULL,
  INDEX idx_fraud_tenant (tenant_id),
  INDEX idx_fraud_status (status),
  INDEX idx_fraud_severity (severity)
);
