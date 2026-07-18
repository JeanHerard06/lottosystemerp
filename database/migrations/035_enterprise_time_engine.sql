-- Enterprise Time Engine defaults.
-- Timezone remains configurable per tenant through tenant_settings.
INSERT INTO tenant_settings (tenant_id, setting_key, setting_value)
SELECT t.id, 'timezone', 'America/Port-au-Prince'
FROM tenants t
LEFT JOIN tenant_settings s ON s.tenant_id=t.id AND s.setting_key='timezone'
WHERE s.tenant_id IS NULL;

INSERT INTO tenant_settings (tenant_id, setting_key, setting_value)
SELECT t.id, 'locale', 'fr_HT'
FROM tenants t
LEFT JOIN tenant_settings s ON s.tenant_id=t.id AND s.setting_key='locale'
WHERE s.tenant_id IS NULL;

INSERT INTO tenant_settings (tenant_id, setting_key, setting_value)
SELECT t.id, 'time_format', '24h'
FROM tenants t
LEFT JOIN tenant_settings s ON s.tenant_id=t.id AND s.setting_key='time_format'
WHERE s.tenant_id IS NULL;
