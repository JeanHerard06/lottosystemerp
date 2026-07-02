CREATE DATABASE IF NOT EXISTS lotto_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lotto_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs, agent_transactions, gains, fiche_details, fiches, tirages, commissions, primes, rates, limites, blocages, marriages, lotteries, supervisors, agents, agencies, role_permissions, user_roles, permissions, roles, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','superviseur','agent') NOT NULL DEFAULT 'agent',
  status TINYINT NOT NULL DEFAULT 1,
  api_token VARCHAR(100) NULL,
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
  code VARCHAR(20) NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  address TEXT NULL,
  phone VARCHAR(50) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE agents (
  id INT AUTO_INCREMENT PRIMARY KEY,
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
  user_id INT NOT NULL,
  agency_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_supervisors_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_supervisors_agency FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE lotteries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  status TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE tirages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lottery_id INT NULL,
  draw_name VARCHAR(100) NOT NULL,
  first_number VARCHAR(10) NOT NULL,
  second_number VARCHAR(10) NULL,
  third_number VARCHAR(10) NULL,
  draw_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tirages_lottery FOREIGN KEY (lottery_id) REFERENCES lotteries(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE fiches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  agent_id INT NOT NULL,
  lottery_id INT NULL,
  fiche_code VARCHAR(50) NOT NULL UNIQUE,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  gain_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('pending','won','lost','paid','cancelled') NOT NULL DEFAULT 'pending',
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
  fiche_detail_id INT NOT NULL,
  tirage_id INT NOT NULL,
  amount_played DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  amount_won DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('won','lost') NOT NULL DEFAULT 'lost',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gains_detail FOREIGN KEY (fiche_detail_id) REFERENCES fiche_details(id) ON DELETE CASCADE,
  CONSTRAINT fk_gains_tirage FOREIGN KEY (tirage_id) REFERENCES tirages(id) ON DELETE CASCADE,
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
  number_value VARCHAR(10) NOT NULL UNIQUE,
  max_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE blocages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  number_value VARCHAR(10) NOT NULL,
  motif VARCHAR(255) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_blocages_number_status (number_value, status)
) ENGINE=InnoDB;

CREATE TABLE marriages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  number1 VARCHAR(5) NOT NULL,
  number2 VARCHAR(5) NOT NULL,
  payout DECIMAL(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB;

CREATE TABLE commissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  agent_id INT NOT NULL,
  game_type VARCHAR(30) NOT NULL,
  percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commissions_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
  UNIQUE KEY uq_agent_game_commission (agent_id, game_type)
) ENGINE=InnoDB;

CREATE TABLE agent_transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  agent_id INT NOT NULL,
  type ENUM('depot','retrait','commission','gain','vente') NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transactions_agent FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action_type VARCHAR(100) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (name, username, password, role, status) VALUES
('Admin Principal', 'admin', '$2y$12$bG75RzrCAO79Zf.1or6JI.l1y4hQnHDHNPUKUalBGyXF7dcCsXR0a', 'admin', 1);
-- Password: admin123

INSERT INTO roles (name, slug) VALUES
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
('Gérer tirages', 'tirages.manage', 'tirages'),
('Voir gagnants', 'gains.view', 'gains'),
('Gérer contrôles', 'controls.manage', 'controls'),
('Gérer finances', 'finances.manage', 'finances'),
('Voir rapports', 'reports.view', 'reports');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.slug='admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN (
  'dashboard.view','agents.view','agents.manage','fiches.view','tirages.manage','gains.view','reports.view'
) WHERE r.slug='superviseur';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN (
  'dashboard.view','fiches.create','fiches.view'
) WHERE r.slug='agent';

INSERT INTO user_roles (user_id, role_id)
SELECT u.id, r.id FROM users u JOIN roles r ON r.slug = u.role WHERE u.username='admin';

INSERT INTO agencies (code, name, address, phone, status) VALUES
('PV', 'Petion-Ville', NULL, NULL, 'active'),
('DEL', 'Delmas', NULL, NULL, 'active'),
('CAP', 'Cap-Haïtien', NULL, NULL, 'active'),
('GON', 'Gonaïves', NULL, NULL, 'active');

INSERT INTO primes (game_type, payout_rate) VALUES
('borlette', 50), ('mariage', 500), ('lotto3', 1000), ('lotto4', 5000);

INSERT INTO rates (play_type, multiplier) VALUES
('borlette', 50), ('mariage', 500), ('lotto3', 1000), ('lotto4', 5000);

INSERT INTO lotteries (name, status) VALUES
('Florida Midi', 1), ('Florida Soir', 1), ('New York Midday', 1), ('New York Evening', 1);
