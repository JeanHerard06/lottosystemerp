-- v2.0 Plugin / Marketplace Foundation

CREATE TABLE IF NOT EXISTS plugins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  version VARCHAR(50) DEFAULT '1.0.0',
  description TEXT NULL,
  category VARCHAR(100) NULL,
  status ENUM('available','installed','disabled','deprecated') DEFAULT 'available',
  is_core TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tenant_plugins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  plugin_id INT NOT NULL,
  status ENUM('enabled','disabled') DEFAULT 'disabled',
  enabled_by INT NULL,
  enabled_at DATETIME NULL,
  disabled_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tenant_plugin (tenant_id, plugin_id),
  INDEX idx_tenant_plugins_tenant (tenant_id),
  INDEX idx_tenant_plugins_plugin (plugin_id)
);

CREATE TABLE IF NOT EXISTS plugin_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  plugin_id INT NOT NULL,
  setting_key VARCHAR(150) NOT NULL,
  setting_value TEXT NULL,
  is_encrypted TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plugin_setting (tenant_id, plugin_id, setting_key),
  INDEX idx_plugin_settings_tenant (tenant_id),
  INDEX idx_plugin_settings_plugin (plugin_id)
);

CREATE TABLE IF NOT EXISTS plugin_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  plugin_id INT NULL,
  event_name VARCHAR(150) NOT NULL,
  payload JSON NULL,
  status ENUM('pending','processed','failed') DEFAULT 'pending',
  error_message TEXT NULL,
  processed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_plugin_events_tenant (tenant_id),
  INDEX idx_plugin_events_name (event_name),
  INDEX idx_plugin_events_status (status)
);

INSERT IGNORE INTO plugins (code, name, version, description, category, status) VALUES
('moncash', 'MonCash Payments', '1.0.0', 'Haitian mobile money payment gateway.', 'payments', 'available'),
('natcash', 'NatCash Payments', '1.0.0', 'Haitian mobile money payment gateway.', 'payments', 'available'),
('stripe', 'Stripe Payments', '1.0.0', 'International card payments.', 'payments', 'available'),
('paypal', 'PayPal Payments', '1.0.0', 'PayPal payment integration.', 'payments', 'available'),
('whatsapp', 'WhatsApp Notifications', '1.0.0', 'WhatsApp notification gateway.', 'notifications', 'available'),
('sms', 'SMS Gateway', '1.0.0', 'SMS notifications provider.', 'notifications', 'available'),
('ai_analytics', 'AI Analytics', '1.0.0', 'AI risk and business analytics.', 'analytics', 'available'),
('advanced_reports', 'Advanced Reports', '1.0.0', 'Advanced reporting and exports.', 'reports', 'available');

INSERT IGNORE INTO permissions (name, slug, module) VALUES
('View Plugins', 'plugins.view', 'plugins'),
('Manage Plugins', 'plugins.manage', 'plugins'),
('Install Plugins', 'plugins.install', 'plugins'),
('Enable Plugins', 'plugins.enable', 'plugins'),
('Configure Plugins', 'plugins.configure', 'plugins');
