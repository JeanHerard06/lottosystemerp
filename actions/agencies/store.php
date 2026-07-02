<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_post();
verify_csrf();
require_permission($pdo, 'agencies.manage');

$code = strtoupper(input_string('code', 20));
$name = input_string('name', 100);
$phone = input_string('phone', 50, false);
$address = input_string('address', 1000, false);
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';
$tenantId = tenant_required_insert_id();

if (is_super_admin()) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tenants WHERE id=? AND status='active'");
    $stmt->execute([$tenantId]);
    if (!$stmt->fetchColumn()) { die('Tenant invalide ou inactif.'); }
}

try {
    $stmt = $pdo->prepare('INSERT INTO agencies (tenant_id, code, name, address, phone, status) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$tenantId, $code, $name, $address, $phone, $status]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'CREATE_AGENCY', 'Agence créée: ' . $code . ' tenant #' . $tenantId);
    redirect('../../views/agencies/index.php');
} catch (Throwable $e) {
    die('Erreur création agence: ' . e($e->getMessage()));
}
