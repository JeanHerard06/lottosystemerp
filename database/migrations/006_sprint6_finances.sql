-- Sprint 6 - Finances, commissions, ledger
ALTER TABLE agent_transactions
  ADD COLUMN reference_no VARCHAR(50) NULL AFTER id,
  ADD COLUMN status ENUM('posted','void') NOT NULL DEFAULT 'posted' AFTER description,
  ADD COLUMN created_by INT NULL AFTER status,
  ADD COLUMN voided_at DATETIME NULL AFTER created_by,
  ADD COLUMN voided_by INT NULL AFTER voided_at,
  ADD INDEX idx_agent_transactions_agent_date (agent_id, created_at),
  ADD INDEX idx_agent_transactions_type_status (type, status),
  ADD CONSTRAINT fk_transactions_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_transactions_voided_by FOREIGN KEY (voided_by) REFERENCES users(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS cash_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cash_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
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

INSERT INTO cash_accounts(name, balance, status)
SELECT 'Caisse principale', 0.00, 'active'
WHERE NOT EXISTS (SELECT 1 FROM cash_accounts WHERE name='Caisse principale');

INSERT IGNORE INTO permissions (name, slug, module) VALUES
('Voir finances', 'finances.view', 'finances'),
('Gérer commissions', 'commissions.manage', 'commissions'),
('Annuler transaction', 'transactions.void', 'finances');

INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('finances.view','finances.manage','commissions.manage','transactions.void') WHERE r.slug='admin';
