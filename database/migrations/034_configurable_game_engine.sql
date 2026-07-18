-- Configurable Game Engine (tenant-aware)
CREATE TABLE IF NOT EXISTS game_types (
  id INT NOT NULL AUTO_INCREMENT,
  tenant_id INT NOT NULL DEFAULT 0,
  code VARCHAR(50) NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT NOT NULL DEFAULT 0,
  min_digits INT NOT NULL DEFAULT 1,
  max_digits INT NOT NULL DEFAULT 20,
  validation_pattern VARCHAR(255) NULL,
  input_hint VARCHAR(120) NULL,
  matching_engine VARCHAR(50) NOT NULL DEFAULT 'exact_first',
  allow_duplicate TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_game_types_tenant_code (tenant_id, code),
  KEY idx_game_types_tenant_enabled (tenant_id, enabled, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_payout_rules (
  id INT NOT NULL AUTO_INCREMENT,
  tenant_id INT NOT NULL DEFAULT 0,
  lottery_id INT NOT NULL DEFAULT 0,
  game_code VARCHAR(50) NOT NULL,
  match_level VARCHAR(50) NOT NULL DEFAULT 'exact',
  multiplier DECIMAL(12,4) NOT NULL DEFAULT 0,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_game_payout_scope (tenant_id, lottery_id, game_code, match_level),
  KEY idx_game_payout_lookup (game_code, tenant_id, lottery_id, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO game_types
(tenant_id, code, name, description, enabled, display_order, min_digits, max_digits, validation_pattern, input_hint, matching_engine, allow_duplicate)
VALUES
(0, 'borlette', 'Bòlèt', 'Numéro à deux chiffres; paiement selon 1er, 2e ou 3e lot.', 1, 10, 2, 2, '^[0-9]{2}$', '2 chif, ex: 12', 'borlette_position', 0),
(0, 'mariage', 'Mariage', 'Deux numéros à deux chiffres.', 1, 20, 4, 5, '^[0-9]{2}-[0-9]{2}$', 'ex: 12-45', 'marriage_any', 0),
(0, 'lotto3', 'Lotto 3', 'Combinaison exacte de trois chiffres.', 1, 30, 3, 3, '^[0-9]{3}$', '3 chif, ex: 123', 'exact_sequence3', 0),
(0, 'lotto4', 'Lotto 4', 'Numéro exact de quatre chiffres.', 1, 40, 4, 4, '^[0-9]{4}$', '4 chif, ex: 1234', 'exact_first', 0)
ON DUPLICATE KEY UPDATE
  name=VALUES(name), description=VALUES(description), validation_pattern=VALUES(validation_pattern),
  input_hint=VALUES(input_hint), matching_engine=VALUES(matching_engine), min_digits=VALUES(min_digits), max_digits=VALUES(max_digits);

INSERT INTO game_payout_rules (tenant_id, lottery_id, game_code, match_level, multiplier, enabled)
VALUES
(0, 0, 'borlette', 'position_1', 60, 1),
(0, 0, 'borlette', 'position_2', 20, 1),
(0, 0, 'borlette', 'position_3', 10, 1),
(0, 0, 'mariage', 'exact', 500, 1),
(0, 0, 'lotto3', 'exact', 1000, 1),
(0, 0, 'lotto4', 'exact', 5000, 1)
ON DUPLICATE KEY UPDATE multiplier=VALUES(multiplier), enabled=VALUES(enabled);
