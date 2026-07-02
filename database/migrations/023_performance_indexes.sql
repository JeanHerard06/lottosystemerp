-- Migration 023 — Performance Indexes RC1

CREATE INDEX IF NOT EXISTS idx_fiches_tenant_date ON fiches(tenant_id, created_at);
CREATE INDEX IF NOT EXISTS idx_fiches_agent_date ON fiches(agent_id, created_at);
CREATE INDEX IF NOT EXISTS idx_fiches_lottery_date ON fiches(lottery_id, created_at);

CREATE INDEX IF NOT EXISTS idx_fiche_details_fiche ON fiche_details(fiche_id);
CREATE INDEX IF NOT EXISTS idx_fiche_details_number ON fiche_details(number_played);

CREATE INDEX IF NOT EXISTS idx_gains_tenant_status ON gains(tenant_id, status);
CREATE INDEX IF NOT EXISTS idx_gains_fiche ON gains(fiche_id);

CREATE INDEX IF NOT EXISTS idx_agent_transactions_tenant_date ON agent_transactions(tenant_id, created_at);
CREATE INDEX IF NOT EXISTS idx_agent_transactions_agent_date ON agent_transactions(agent_id, created_at);

CREATE INDEX IF NOT EXISTS idx_cash_sessions_tenant_status ON cash_sessions(tenant_id, status);
CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX IF NOT EXISTS idx_audit_logs_tenant_date ON audit_logs(tenant_id, created_at);
