CREATE DATABASE IF NOT EXISTS lotto_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lotto_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tenant_registrations, subscription_payments, subscription_invoices, payment_methods, subscription_plans, cash_movements, cash_accounts, audit_logs, agent_transactions, gains, fiche_details, fiches, tirages, commissions, primes, rates, limites, blocages, marriages, lotteries, supervisors, agents, agencies, role_permissions, user_roles, permissions, roles, users, tenant_subscriptions, tenant_settings, tenants;
SET FOREIGN_KEY_CHECKS = 1;


CREATE TABLE tenants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  plan ENUM('basic','pro','enterprise') NOT NULL DEFAULT 'basic',
  status ENUM('active','suspended','cancelled') NOT NULL DEFAULT 'active',
  expires_at DATE NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tenant_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tenant_setting (tenant_id, setting_key),
  CONSTRAINT fk_tenant_settings_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tenant_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  plan ENUM('basic','pro','enterprise') NOT NULL DEFAULT 'basic',
  status ENUM('trial','active','past_due','cancelled') NOT NULL DEFAULT 'trial',
  starts_at DATE NULL,
  ends_at DATE NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_provider VARCHAR(50) NULL,
  reference_no VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tenant_subscriptions_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE tenant_registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  business_name VARCHAR(150) NOT NULL,
  owner_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  address VARCHAR(255) NULL,
  requested_plan ENUM('basic','pro','enterprise') NOT NULL DEFAULT 'basic',
  notes TEXT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_username VARCHAR(80) NULL,
  approved_by INT NULL,
  approved_at DATETIME NULL,
  rejected_by INT NULL,
  rejected_at DATETIME NULL,
  rejection_reason TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tenant_registrations_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
  INDEX idx_tenant_registrations_status (status),
  INDEX idx_tenant_registrations_email (email)
) ENGINE=InnoDB;


CREATE TABLE users (
  tenant_id INT NULL,
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin','tenant_admin','admin','superviseur','agent') NOT NULL DEFAULT 'agent',
  status TINYINT NOT NULL DEFAULT 1,
  api_token VARCHAR(100) NULL,
  mobile_token VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  module VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE user_roles (
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  role_id INT NOT NULL,
  permission_id INT NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE agencies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  code VARCHAR(20) NULL,
  name VARCHAR(100) NOT NULL,
  address TEXT NULL,
  phone VARCHAR(50) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_agencies_tenant_code (tenant_id, code),
  INDEX idx_agencies_tenant_status (tenant_id, status)
) ENGINE=InnoDB;

CREATE TABLE agents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NOT NULL,
  agency_id INT NULL,
  phone VARCHAR(30) NULL,
  commission DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  borlette_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
  mariage_rate DECIMAL(5,2) NOT NULL DEFAULT 12.00,
  lotto3_rate DECIMAL(5,2) NOT NULL DEFAULT 15.00,
  lotto4_rate DECIMAL(5,2) NOT NULL DEFAULT 20.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_agents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_agents_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE supervisors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NOT NULL,
  agency_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_supervisors_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_supervisors_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE lotteries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  name VARCHAR(100) NOT NULL,
  status TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE tirages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  lottery_id INT NULL,
  draw_name VARCHAR(100) NOT NULL,
  first_number VARCHAR(10) NOT NULL,
  second_number VARCHAR(10) NULL,
  third_number VARCHAR(10) NULL,
  draw_date DATE NOT NULL,
  status ENUM('open','processed','cancelled') NOT NULL DEFAULT 'open',
  processed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tirages_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE fiches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  agent_id INT NOT NULL,
  lottery_id INT NULL,
  fiche_code VARCHAR(50) NOT NULL UNIQUE,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  gain_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('pending','won','lost','paid','cancelled') NOT NULL DEFAULT 'pending',
  sync_source ENUM('web','mobile') NOT NULL DEFAULT 'web',
  device_id VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fiches_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE RESTRICT,
  CONSTRAINT fk_fiches_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE fiche_details (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fiche_id INT NOT NULL,
  number_played VARCHAR(10) NOT NULL,
  play_type ENUM('borlette','mariage','lotto3','lotto4') NOT NULL DEFAULT 'borlette',
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_details_fiche FOREIGN KEY (fiche_id) REFERENCES fiches(id) ON DELETE CASCADE,
  INDEX idx_details_number (number_played)
) ENGINE=InnoDB;

CREATE TABLE gains (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  fiche_detail_id INT NOT NULL,
  tirage_id INT NOT NULL,
  amount_played DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  amount_won DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('won','lost') NOT NULL DEFAULT 'lost',
  is_paid TINYINT NOT NULL DEFAULT 0,
  paid_at DATETIME NULL,
  paid_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_gains_tenant_status (tenant_id, status),
  CONSTRAINT fk_gains_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
  CONSTRAINT fk_gains_detail FOREIGN KEY (fiche_detail_id) REFERENCES fiche_details(id) ON DELETE CASCADE,
  CONSTRAINT fk_gains_tirage FOREIGN KEY (tirage_id) REFERENCES tirages(id) ON DELETE CASCADE,
  CONSTRAINT fk_gains_paid_by FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY uq_gain_detail_tirage (fiche_detail_id, tirage_id)
) ENGINE=InnoDB;

CREATE TABLE primes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  game_type VARCHAR(30) NOT NULL UNIQUE,
  payout_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  play_type VARCHAR(20) NOT NULL UNIQUE,
  multiplier DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB;

CREATE TABLE limites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  number_value VARCHAR(10) NOT NULL,
  game_type VARCHAR(30) NULL,
  lottery_id INT NULL,
  agency_id INT NULL,
  max_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  threshold_percent DECIMAL(5,2) NOT NULL DEFAULT 80.00,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_limites_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL,
  CONSTRAINT fk_limites_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL,
  UNIQUE KEY uq_limites_scope (number_value, game_type, lottery_id, agency_id),
  INDEX idx_limites_scope (number_value, game_type, lottery_id, agency_id, status)
) ENGINE=InnoDB;

CREATE TABLE blocages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  number_value VARCHAR(10) NULL,
  game_type VARCHAR(30) NULL,
  lottery_id INT NULL,
  agency_id INT NULL,
  motif VARCHAR(255) NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_blocages_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL,
  CONSTRAINT fk_blocages_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL,
  INDEX idx_blocages_number_status (number_value, status),
  INDEX idx_blocages_scope (number_value, game_type, lottery_id, agency_id, status)
) ENGINE=InnoDB;

CREATE TABLE marriages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  number1 VARCHAR(5) NOT NULL,
  number2 VARCHAR(5) NOT NULL,
  game_type VARCHAR(30) NOT NULL DEFAULT 'mariage',
  lottery_id INT NULL,
  payout DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_marriages_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE commissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  agent_id INT NOT NULL,
  game_type VARCHAR(30) NOT NULL,
  percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commissions_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
  UNIQUE KEY uq_agent_game_commission (agent_id, game_type)
) ENGINE=InnoDB;

CREATE TABLE agent_transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  reference_no VARCHAR(50) NULL,
  agent_id INT NOT NULL,
  type ENUM('depot','retrait','commission','gain','vente') NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  description TEXT NULL,
  status ENUM('posted','void') NOT NULL DEFAULT 'posted',
  created_by INT NULL,
  voided_at DATETIME NULL,
  voided_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
  CONSTRAINT fk_transactions_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_transactions_voided_by FOREIGN KEY (voided_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_agent_transactions_agent_date (agent_id, created_at),
  INDEX idx_agent_transactions_type_status (type, status)
) ENGINE=InnoDB;

CREATE TABLE cash_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  name VARCHAR(100) NOT NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE cash_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  cash_account_id INT NOT NULL,
  direction ENUM('in','out') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  source_type VARCHAR(50) NULL,
  source_id INT NULL,
  description TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cash_movements_account FOREIGN KEY (cash_account_id) REFERENCES cash_accounts(id) ON DELETE CASCADE,
  CONSTRAINT fk_cash_movements_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  user_id INT NULL,
  action_type VARCHAR(100) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- Sprint 9 indexes for release readiness
CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_fiches_created_status ON fiches(created_at, status);
CREATE INDEX idx_fiches_agent_date ON fiches(agent_id, created_at);
CREATE INDEX idx_tirages_date_status ON tirages(draw_date, status);
CREATE INDEX idx_gains_status_paid ON gains(status, is_paid);
CREATE INDEX idx_audit_user_date ON audit_logs(user_id, created_at);

INSERT INTO users (tenant_id, name, username, password, role, status) VALUES
(NULL, 'Admin Principal', 'admin', '$2y$12$bG75RzrCAO79Zf.1or6JI.l1y4hQnHDHNPUKUalBGyXF7dcCsXR0a', 'super_admin', 1);
-- Password: admin123

INSERT INTO roles (name, slug) VALUES
('Super Admin', 'super_admin'),
('Tenant Admin', 'tenant_admin'),
('Administrateur', 'admin'),
('Superviseur', 'superviseur'),
('Agent', 'agent');

INSERT INTO permissions (name, slug, module) VALUES
('Voir dashboard', 'dashboard.view', 'dashboard'),
('Gérer utilisateurs', 'users.manage', 'users'),
('Gérer rôles', 'roles.manage', 'roles'),
('Voir agents', 'agents.view', 'agents'),
('Gérer agents', 'agents.manage', 'agents'),
('Gérer agences', 'agencies.manage', 'agencies'),
('Gérer superviseurs', 'supervisors.manage', 'supervisors'),
('Créer fiches', 'fiches.create', 'fiches'),
('Voir fiches', 'fiches.view', 'fiches'),
('Annuler fiches', 'fiches.cancel', 'fiches'),
('Gérer lotteries', 'lotteries.manage', 'lotteries'),
('Gérer tirages', 'tirages.manage', 'tirages'),
('Voir gagnants', 'gains.view', 'gains'),
('Calculer gains', 'gains.calculate', 'gains'),
('Payer gains', 'gains.pay', 'gains'),
('Gérer contrôles', 'controls.manage', 'controls'),
('Voir finances', 'finances.view', 'finances'),
('Gérer finances', 'finances.manage', 'finances'),
('Gérer commissions', 'commissions.manage', 'commissions'),
('Annuler transaction', 'transactions.void', 'finances'),
('Voir rapports', 'reports.view', 'reports'),
('Voir dashboard risque', 'risk.view', 'risk'),
('Gérer paramètres', 'settings.manage', 'settings'),
('Utiliser API', 'api.use', 'api'),
('Voir super admin', 'superadmin.view', 'saas'),
('Gérer tenants', 'tenants.manage', 'saas'),
('Gérer demandes tenant', 'tenant_registrations.manage', 'saas'),
('Voir audit sécurité tenant', 'tenant.security.view', 'saas');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.slug='super_admin';


INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN (
  'dashboard.view','users.manage','agents.view','agents.manage','agencies.manage','supervisors.manage',
  'fiches.create','fiches.view','fiches.cancel','lotteries.manage','tirages.manage','gains.view','gains.calculate','gains.pay',
  'controls.manage','risk.view','finances.view','finances.manage','commissions.manage','transactions.void','reports.view','settings.manage','api.use'
) WHERE r.slug='tenant_admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN (
  'dashboard.view','agents.view','agents.manage','fiches.view','fiches.cancel','lotteries.manage','tirages.manage','gains.view','gains.calculate','gains.pay','reports.view','risk.view'
) WHERE r.slug='superviseur';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN (
  'dashboard.view','fiches.create','fiches.view'
) WHERE r.slug='agent';

INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id FROM users u JOIN roles r ON r.slug = u.role WHERE u.username='admin';

INSERT INTO tenants(id, name, slug, plan, status) VALUES (1, 'Default Bank', 'default', 'enterprise', 'active');

UPDATE users SET tenant_id = 1 WHERE tenant_id IS NULL AND role <> 'super_admin';

INSERT INTO agencies (tenant_id, code, name, address, phone, status) VALUES
(1, 'PV', 'Petion-Ville', NULL, NULL, 'active'),
(1, 'DEL', 'Delmas', NULL, NULL, 'active'),
(1, 'CAP', 'Cap-Haïtien', NULL, NULL, 'active'),
(1, 'GON', 'Gonaïves', NULL, NULL, 'active');

INSERT INTO primes (game_type, payout_rate) VALUES
('borlette', 50), ('mariage', 500), ('lotto3', 1000), ('lotto4', 5000);

INSERT INTO rates (play_type, multiplier) VALUES
('borlette', 50), ('mariage', 500), ('lotto3', 1000), ('lotto4', 5000);

INSERT INTO lotteries (tenant_id, name, status) VALUES
(1, 'Florida Midi', 1), (1, 'Florida Soir', 1), (1, 'New York Midday', 1), (1, 'New York Evening', 1);

INSERT INTO cash_accounts(tenant_id, name, balance, status) VALUES (1, 'Caisse principale', 0.00, 'active');

-- Sprint 10 tenant indexes
CREATE INDEX idx_users_tenant ON users(tenant_id);
CREATE INDEX idx_fiches_tenant_date ON fiches(tenant_id, created_at);
CREATE INDEX idx_agents_tenant ON agents(tenant_id);
CREATE INDEX idx_lotteries_tenant_status ON lotteries(tenant_id, status);


-- Sprint 11: Paiements & Abonnements SaaS

CREATE TABLE IF NOT EXISTS subscription_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  price_monthly DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  price_yearly DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  max_agents INT NULL,
  max_agencies INT NULL,
  features TEXT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payment_methods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  provider VARCHAR(80) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subscription_invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  subscription_id INT NULL,
  invoice_no VARCHAR(60) NOT NULL UNIQUE,
  period_start DATE NULL,
  period_end DATE NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('draft','issued','partial','paid','void','overdue') NOT NULL DEFAULT 'issued',
  due_date DATE NULL,
  notes TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_subscription_invoices_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_subscription_invoices_subscription FOREIGN KEY (subscription_id) REFERENCES tenant_subscriptions(id) ON DELETE SET NULL,
  CONSTRAINT fk_subscription_invoices_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subscription_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  invoice_id INT NULL,
  payment_method_id INT NULL,
  amount DECIMAL(12,2) NOT NULL,
  paid_at DATETIME NOT NULL,
  reference_no VARCHAR(100) NULL,
  status ENUM('pending','completed','failed','refunded','void') NOT NULL DEFAULT 'completed',
  notes TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_subscription_payments_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  CONSTRAINT fk_subscription_payments_invoice FOREIGN KEY (invoice_id) REFERENCES subscription_invoices(id) ON DELETE SET NULL,
  CONSTRAINT fk_subscription_payments_method FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL,
  CONSTRAINT fk_subscription_payments_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO subscription_plans(code, name, price_monthly, price_yearly, max_agents, max_agencies, features) VALUES
('basic', 'Basic', 49.00, 499.00, 10, 1, 'Dashboard, agents, fiches, tirages'),
('pro', 'Professional', 99.00, 999.00, 50, 5, 'Rapports, finances, PWA agent, sauvegardes'),
('enterprise', 'Enterprise', 199.00, 1999.00, NULL, NULL, 'Multi-agences illimité, API, support prioritaire');

INSERT IGNORE INTO payment_methods(code, name, provider) VALUES
('cash', 'Cash', 'manual'),
('moncash', 'MonCash', 'manual'),
('natcash', 'NatCash', 'manual'),
('stripe', 'Stripe', 'stripe'),
('paypal', 'PayPal', 'paypal');

INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Gérer plans SaaS', 'plans.manage', 'saas'),
('Gérer abonnements SaaS', 'subscriptions.manage', 'saas'),
('Voir paiements SaaS', 'payments.view', 'saas'),
('Créer paiements SaaS', 'payments.create', 'saas');

INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.slug IN ('plans.manage','subscriptions.manage','payments.view','payments.create')
WHERE r.slug='admin';

CREATE INDEX idx_subscription_invoices_tenant_status ON subscription_invoices(tenant_id, status);
CREATE INDEX idx_subscription_payments_tenant_paid_at ON subscription_payments(tenant_id, paid_at);
CREATE INDEX idx_tenant_subscriptions_tenant_status ON tenant_subscriptions(tenant_id, status);



INSERT IGNORE INTO permissions(name, slug, module) VALUES
('Utiliser API mobile', 'mobile.api.use', 'mobile'),
('Créer fiche mobile', 'mobile.fiches.create', 'mobile'),
('Voir dashboard mobile', 'mobile.dashboard.view', 'mobile');


-- Sprint 20 audit logs center
INSERT IGNORE INTO permissions (name, slug, module) VALUES
('Voir journal audit', 'logs.view', 'logs'),
('Gérer journal audit', 'logs.manage', 'logs');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('logs.view','logs.manage')
WHERE r.slug IN ('super_admin','admin','tenant_admin');

CREATE INDEX idx_audit_tenant_date ON audit_logs(tenant_id, created_at);
CREATE INDEX idx_audit_action_date ON audit_logs(action_type, created_at);
