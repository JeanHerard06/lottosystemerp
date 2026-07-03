-- Dashboard v2 + BI monitor foundation

CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NULL,
    role_slug VARCHAR(80) NOT NULL,
    widget_key VARCHAR(120) NOT NULL,
    title VARCHAR(160) NOT NULL,
    position_order INT DEFAULT 0,
    is_enabled TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dashboard_widgets_tenant_role (tenant_id, role_slug),
    UNIQUE KEY uq_dashboard_widget_role_key (tenant_id, role_slug, widget_key)
);

CREATE TABLE IF NOT EXISTS bi_snapshots (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NULL,
    snapshot_date DATE NOT NULL,
    metric_key VARCHAR(120) NOT NULL,
    metric_value DECIMAL(18,2) DEFAULT 0,
    meta_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bi_snapshots_tenant_date (tenant_id, snapshot_date),
    INDEX idx_bi_snapshots_metric (metric_key)
);

INSERT IGNORE INTO dashboard_widgets (tenant_id, role_slug, widget_key, title, position_order) VALUES
(NULL,'super_admin','tenants_active','Tenants actifs',1),
(NULL,'super_admin','saas_revenue','Revenus SaaS',2),
(NULL,'tenant_admin','sales_today','Ventes aujourd’hui',1),
(NULL,'tenant_admin','profit_today','Profit aujourd’hui',2),
(NULL,'superviseur','agent_sales','Ventes par agent',1),
(NULL,'agent','my_sales','Mes ventes',1);
