-- Migration 026 - Lottery Engine 2.0 draft

CREATE TABLE IF NOT EXISTS games (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  code VARCHAR(50) NOT NULL,
  name VARCHAR(120) NOT NULL,
  game_type ENUM('borlette','mariage','pick2','pick3','pick4','custom') DEFAULT 'custom',
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_games_tenant_code (tenant_id, code),
  INDEX idx_games_tenant (tenant_id)
);

CREATE TABLE IF NOT EXISTS lottery_schedules_v2 (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  lottery_id INT NOT NULL,
  game_id INT NULL,
  day_of_week TINYINT NULL,
  draw_time TIME NOT NULL,
  close_before_minutes INT DEFAULT 10,
  timezone VARCHAR(80) DEFAULT 'America/Port-au-Prince',
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_lsv2_tenant_lottery (tenant_id, lottery_id),
  INDEX idx_lsv2_game (game_id)
);

CREATE TABLE IF NOT EXISTS sales_windows (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  lottery_id INT NOT NULL,
  game_id INT NULL,
  schedule_id INT NULL,
  opens_at DATETIME NOT NULL,
  closes_at DATETIME NOT NULL,
  status ENUM('open','closed','drawn','cancelled') DEFAULT 'open',
  closed_by INT NULL,
  closed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sales_windows_tenant_status (tenant_id, status),
  INDEX idx_sales_windows_lottery (lottery_id)
);

CREATE TABLE IF NOT EXISTS draws_v2 (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  lottery_id INT NOT NULL,
  game_id INT NULL,
  sales_window_id INT NULL,
  draw_code VARCHAR(80) NOT NULL,
  draw_datetime DATETIME NOT NULL,
  status ENUM('scheduled','closed','result_entered','calculated','paid','cancelled') DEFAULT 'scheduled',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_draws_tenant_code (tenant_id, draw_code),
  INDEX idx_draws_tenant_status (tenant_id, status)
);

CREATE TABLE IF NOT EXISTS draw_results_v2 (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  draw_id INT NOT NULL,
  position_no INT NOT NULL DEFAULT 1,
  result_number VARCHAR(20) NOT NULL,
  entered_by INT NULL,
  validated_by INT NULL,
  validated_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_results_draw (draw_id),
  INDEX idx_results_tenant (tenant_id)
);

CREATE TABLE IF NOT EXISTS prize_rules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NULL,
  game_id INT NOT NULL,
  play_type VARCHAR(50) NOT NULL,
  position_no INT DEFAULT 1,
  multiplier DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_prize_rule (tenant_id, game_id, play_type, position_no),
  INDEX idx_prize_rules_tenant (tenant_id)
);
