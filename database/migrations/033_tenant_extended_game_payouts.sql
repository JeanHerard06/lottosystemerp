-- Tenant-configurable payout multipliers for Mariage, Lotto 3 and Lotto 4.
-- Scope precedence: tenant+lottery, tenant default, global+lottery, global default.
-- Existing game_settings table is reused; no duplicate columns or tables are introduced.

INSERT INTO game_settings (tenant_id, lottery_id, setting_key, setting_value)
VALUES
(0, 0, 'payout_mariage', 500),
(0, 0, 'payout_lotto3', 1000),
(0, 0, 'payout_lotto4', 5000)
ON DUPLICATE KEY UPDATE setting_value = setting_value;
