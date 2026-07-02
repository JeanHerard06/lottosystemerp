-- Sprint 9 - Quality & Release
-- Run this migration on an existing Sprint 8 database.

ALTER TABLE users DROP COLUMN api_token;
ALTER TABLE users ADD COLUMN api_token VARCHAR(100) NULL AFTER password;

CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_fiches_created_status ON fiches(created_at, status);
CREATE INDEX idx_fiches_agent_date ON fiches(agent_id, created_at);
CREATE INDEX idx_tirages_date_status ON tirages(draw_date, status);
CREATE INDEX idx_gains_status_paid ON gains(status, is_paid);
CREATE INDEX idx_audit_user_date ON audit_logs(user_id, created_at);
