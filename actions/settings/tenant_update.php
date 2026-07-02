<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/settings.php';

require_permission($pdo, 'settings.manage');
csrf_verify();

$tenantId = is_super_admin() ? (int)($_POST['tenant_id'] ?? 0) : (int)current_tenant_id();
if ($tenantId <= 0) { die('Tenant requis.'); }

if (!is_super_admin() && $tenantId !== (int)current_tenant_id()) {
    audit_log($pdo, current_user_id(), 'TENANT_SETTINGS_DENIED', 'Tentative modification paramètres autre tenant ID ' . $tenantId);
    http_response_code(403);
    die('Accès refusé.');
}

$stmt = $pdo->prepare('SELECT id FROM tenants WHERE id=? LIMIT 1');
$stmt->execute([$tenantId]);
if (!$stmt->fetch()) { die('Tenant introuvable.'); }

$keys = [
    'business_name','business_phone','business_address','ticket_subtitle','ticket_footer',
    'primary_color','accent_color','currency','timezone','smtp_host','smtp_port',
    'smtp_user','smtp_from_email','smtp_from_name'
];

$pdo->beginTransaction();
foreach ($keys as $key) {
    $value = trim((string)($_POST[$key] ?? ''));
    save_tenant_setting($pdo, $tenantId, $key, $value);
}

if (!empty($_POST['smtp_password'])) {
    save_tenant_setting($pdo, $tenantId, 'smtp_password', password_hash((string)$_POST['smtp_password'], PASSWORD_DEFAULT));
}

if (!empty($_FILES['logo']['name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
    $mime = mime_content_type($_FILES['logo']['tmp_name']);
    if (!isset($allowed[$mime])) { throw new RuntimeException('Format logo invalide.'); }
    if ((int)$_FILES['logo']['size'] > 2 * 1024 * 1024) { throw new RuntimeException('Logo trop lourd. Max 2MB.'); }
    $dir = __DIR__ . '/../../storage/uploads/tenant_logos';
    if (!is_dir($dir)) { mkdir($dir, 0775, true); }
    $filename = 'tenant_' . $tenantId . '_' . time() . '.' . $allowed[$mime];
    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) { throw new RuntimeException('Upload logo échoué.'); }
    save_tenant_setting($pdo, $tenantId, 'logo_path', '/storage/uploads/tenant_logos/' . $filename);
}

$pdo->commit();
audit_log($pdo, current_user_id(), 'TENANT_SETTINGS_UPDATE', 'Paramètres tenant mis à jour ID ' . $tenantId);

header('Location: /views/settings/tenant.php' . (is_super_admin() ? '?tenant_id=' . $tenantId : ''));
exit;
