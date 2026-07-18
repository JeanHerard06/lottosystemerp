-- Haiti Borlette payout engine: configurable 60 / 20 / 10 by result position.
-- Scope precedence: tenant+lottery, tenant default, global+lottery, global default.

CREATE TABLE IF NOT EXISTS game_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL DEFAULT 0,
  lottery_id INT NOT NULL DEFAULT 0,
  setting_key VARCHAR(80) NOT NULL,
  setting_value DECIMAL(12,4) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_game_settings_scope (tenant_id, lottery_id, setting_key),
  KEY idx_game_settings_lookup (setting_key, tenant_id, lottery_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO game_settings (tenant_id, lottery_id, setting_key, setting_value)
VALUES
(0, 0, 'payout_1', 60),
(0, 0, 'payout_2', 20),
(0, 0, 'payout_3', 10)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

ALTER TABLE gains
  ADD COLUMN winning_position TINYINT NULL AFTER amount_won,
  ADD COLUMN payout_multiplier DECIMAL(12,4) NOT NULL DEFAULT 0 AFTER winning_position;
