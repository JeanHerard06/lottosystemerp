-- Migration 026: Mobile v2 Agent MVP

ALTER TABLE users ADD COLUMN IF NOT EXISTS mobile_refresh_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_mobile_login_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS mobile_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    device_id VARCHAR(120) NOT NULL,
    device_name VARCHAR(150) NULL,
    platform VARCHAR(50) NULL,
    app_version VARCHAR(50) NULL,
    status ENUM('active','blocked') DEFAULT 'active',
    last_seen_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_mobile_device (tenant_id, device_id),
    INDEX idx_mobile_devices_tenant_user (tenant_id, user_id)
);

CREATE TABLE IF NOT EXISTS mobile_sync_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    device_id VARCHAR(120) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    local_uuid VARCHAR(120) NOT NULL,
    server_id INT NULL,
    payload JSON NOT NULL,
    status ENUM('pending','synced','failed') DEFAULT 'pending',
    error_message TEXT NULL,
    synced_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mobile_sync_tenant_status (tenant_id, status),
    INDEX idx_mobile_sync_device (tenant_id, device_id)
);

ALTER TABLE fiches ADD COLUMN IF NOT EXISTS local_uuid VARCHAR(120) NULL;
ALTER TABLE fiches ADD COLUMN IF NOT EXISTS device_id VARCHAR(120) NULL;
ALTER TABLE fiches ADD COLUMN IF NOT EXISTS sync_status ENUM('server','pending','synced','failed') DEFAULT 'server';
ALTER TABLE fiches ADD INDEX IF NOT EXISTS idx_fiches_mobile_sync (tenant_id, device_id, sync_status);
