-- Mobile Agent Pay Gain support.
-- Safe to run after the existing migration runner: duplicate columns/indexes are treated as warnings.
ALTER TABLE gains ADD COLUMN paid_cash_session_id INT NULL AFTER paid_by;
CREATE INDEX idx_gains_paid_cash_session ON gains(paid_cash_session_id);
