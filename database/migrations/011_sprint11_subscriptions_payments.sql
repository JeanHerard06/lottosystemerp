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
