-- v2.0 Financial Ledger Engine

CREATE TABLE IF NOT EXISTS financial_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  code VARCHAR(80) NOT NULL,
  name VARCHAR(150) NOT NULL,
  type ENUM('asset','liability','equity','income','expense') NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_financial_account_tenant_code (tenant_id, code),
  INDEX idx_financial_accounts_tenant (tenant_id)
);

CREATE TABLE IF NOT EXISTS journal_entries (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  entry_no VARCHAR(80) NOT NULL,
  source_type VARCHAR(80) NOT NULL,
  source_id BIGINT NULL,
  description TEXT NULL,
  status ENUM('draft','posted','cancelled') DEFAULT 'posted',
  posted_by INT NULL,
  posted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_journal_entry_no (tenant_id, entry_no),
  INDEX idx_journal_entries_tenant_source (tenant_id, source_type, source_id),
  INDEX idx_journal_entries_posted_at (posted_at)
);

CREATE TABLE IF NOT EXISTS journal_lines (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  journal_entry_id BIGINT NOT NULL,
  account_id INT NOT NULL,
  debit DECIMAL(14,2) DEFAULT 0,
  credit DECIMAL(14,2) DEFAULT 0,
  memo VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_journal_lines_tenant_account (tenant_id, account_id),
  INDEX idx_journal_lines_entry (journal_entry_id),
  CONSTRAINT fk_journal_lines_entry FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
  CONSTRAINT fk_journal_lines_account FOREIGN KEY (account_id) REFERENCES financial_accounts(id)
);

CREATE TABLE IF NOT EXISTS ledger_balances (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  account_id INT NOT NULL,
  balance DECIMAL(14,2) DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ledger_balance_account (tenant_id, account_id),
  INDEX idx_ledger_balances_tenant (tenant_id),
  CONSTRAINT fk_ledger_balances_account FOREIGN KEY (account_id) REFERENCES financial_accounts(id)
);

CREATE TABLE IF NOT EXISTS cash_transfers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  from_account_id INT NOT NULL,
  to_account_id INT NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
  requested_by INT NULL,
  approved_by INT NULL,
  approved_at DATETIME NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cash_transfers_tenant_status (tenant_id, status)
);

CREATE TABLE IF NOT EXISTS expenses (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  category VARCHAR(120) NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  description TEXT NULL,
  expense_date DATE NOT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_expenses_tenant_date (tenant_id, expense_date)
);

CREATE TABLE IF NOT EXISTS incomes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  category VARCHAR(120) NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  description TEXT NULL,
  income_date DATE NOT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_incomes_tenant_date (tenant_id, income_date)
);
