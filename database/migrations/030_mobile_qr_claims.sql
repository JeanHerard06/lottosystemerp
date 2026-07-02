CREATE TABLE IF NOT EXISTS ticket_verification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  ticket_id INT NULL,
  verification_code VARCHAR(120) NOT NULL,
  checked_by INT NULL,
  status VARCHAR(40) NOT NULL,
  ip_address VARCHAR(64) NULL,
  device_id VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_tvl_tenant (tenant_id),
  INDEX idx_tvl_code (verification_code)
);

CREATE TABLE IF NOT EXISTS ticket_claims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  ticket_id INT NOT NULL,
  customer_id INT NULL,
  claimed_by INT NULL,
  amount DECIMAL(14,2) DEFAULT 0,
  status ENUM('pending','approved','rejected','paid','cancelled') DEFAULT 'pending',
  notes TEXT NULL,
  approved_by INT NULL,
  approved_at DATETIME NULL,
  paid_by INT NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  INDEX idx_claims_tenant (tenant_id),
  INDEX idx_claims_ticket (ticket_id),
  INDEX idx_claims_status (status)
);
