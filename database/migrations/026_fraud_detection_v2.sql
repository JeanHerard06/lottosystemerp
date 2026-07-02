-- Migration 026: Fraud Detection & Risk Alerts v2.0

CREATE TABLE IF NOT EXISTS fraud_rules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  code VARCHAR(100) NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  severity ENUM('low','medium','high','critical') DEFAULT 'medium',
  threshold_value DECIMAL(12,2) NULL,
  action ENUM('alert','block','require_approval') DEFAULT 'alert',
  status ENUM('active','inactive') DEFAULT 'active',
  created_by INT NULL,
  updated_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_fraud_rule_tenant_code (tenant_id, code),
  INDEX idx_fraud_rules_tenant_status (tenant_id, status)
);

CREATE TABLE IF NOT EXISTS fraud_alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  rule_id INT NULL,
  alert_code VARCHAR(100) NOT NULL,
  severity ENUM('low','medium','high','critical') DEFAULT 'medium',
  entity_type VARCHAR(80) NULL,
  entity_id INT NULL,
  agent_id INT NULL,
  user_id INT NULL,
  title VARCHAR(180) NOT NULL,
  message TEXT NULL,
  risk_score DECIMAL(5,2) DEFAULT 0,
  status ENUM('open','investigating','resolved','dismissed') DEFAULT 'open',
  resolved_by INT NULL,
  resolved_at DATETIME NULL,
  resolution_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fraud_alerts_tenant_status (tenant_id, status),
  INDEX idx_fraud_alerts_entity (entity_type, entity_id),
  INDEX idx_fraud_alerts_severity (severity),
  CONSTRAINT fk_fraud_alerts_rule FOREIGN KEY (rule_id) REFERENCES fraud_rules(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS risk_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  entity_type VARCHAR(80) NOT NULL,
  entity_id INT NOT NULL,
  score DECIMAL(5,2) DEFAULT 0,
  risk_level ENUM('low','medium','high','critical') DEFAULT 'low',
  factors JSON NULL,
  calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_risk_scores_tenant_entity (tenant_id, entity_type, entity_id),
  INDEX idx_risk_scores_level (risk_level)
);

CREATE TABLE IF NOT EXISTS fraud_alert_actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  alert_id INT NOT NULL,
  user_id INT NULL,
  action_type VARCHAR(80) NOT NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fraud_alert_actions_tenant (tenant_id),
  CONSTRAINT fk_fraud_alert_actions_alert FOREIGN KEY (alert_id) REFERENCES fraud_alerts(id) ON DELETE CASCADE
);

INSERT IGNORE INTO fraud_rules (tenant_id, code, name, description, severity, action, status) VALUES
(NULL, 'INVALID_TICKET_CODE', 'Ticket code invalide', 'Tentative de vérification avec code non reconnu.', 'high', 'alert', 'active'),
(NULL, 'DOUBLE_GAIN_PAYMENT', 'Paiement gain doublon', 'Tentative de payer un gain déjà payé.', 'critical', 'block', 'active'),
(NULL, 'SALE_AFTER_CLOSE', 'Vente après fermeture', 'Vente tentée après la fermeture de la lottery.', 'critical', 'block', 'active'),
(NULL, 'CROSS_TENANT_TICKET_ACCESS', 'Accès ticket autre tenant', 'Tentative d’accès à un ticket hors tenant.', 'critical', 'block', 'active'),
(NULL, 'EXCESSIVE_REPRINT', 'Réimpression excessive', 'Réimpression ticket au-delà du seuil autorisé.', 'medium', 'alert', 'active');
