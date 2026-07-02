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

$id = (int)($_POST['id'] ?? 0);
$code = strtoupper(input_string('code', 20));
$name = input_string('name', 100);
$phone = input_string('phone', 50, false);
$address = input_string('address', 1000, false);
$status = in_array($_POST['status'] ?? 'active', ['active','inactive'], true) ? $_POST['status'] : 'active';

$stmt = $pdo->prepare('SELECT * FROM agencies WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$agency = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($agency ?: null, 'agence');

try {
    $stmt = $pdo->prepare('UPDATE agencies SET code=?, name=?, address=?, phone=?, status=? WHERE id=? AND tenant_id=?');
    $stmt->execute([$code, $name, $address, $phone, $status, $id, (int)$agency['tenant_id']]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'UPDATE_AGENCY', 'Agence modifiée: ' . $code . ' tenant #' . (int)$agency['tenant_id']);
    redirect('../../views/agencies/index.php');
} catch (Throwable $e) {
    die('Erreur modification agence: ' . e($e->getMessage()));
}
