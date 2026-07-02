<?php
require "../../config/database.php";
require_once "../../includes/auth.php";
require_once "../../app/Helpers/tenant.php";
require_once "../../app/Helpers/csrf.php";
require_once "../../app/Helpers/security.php";
require_once "../../app/Helpers/audit.php";
require_super_admin();
require_post();
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$name = input_string('name', 120);
$slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', input_string('slug', 80)));
$plan = $_POST['plan'] ?? 'basic';
$status = $_POST['status'] ?? 'active';
if (!in_array($plan, ['basic','pro','enterprise'], true)) { die('Plan invalide.'); }
if (!in_array($status, ['active','suspended','cancelled'], true)) { die('Statut invalide.'); }
$expiresAt = $_POST['expires_at'] ?: null;
$notes = input_string('notes', 500, false);

$stmt = $pdo->prepare("UPDATE tenants SET name=?, slug=?, plan=?, status=?, expires_at=?, notes=? WHERE id=?");
$stmt->execute([$name, $slug, $plan, $status, $expiresAt, $notes, $id]);
audit_log($pdo, current_user_id(), 'TENANT_UPDATE', 'Tenant modifié: #' . $id);
header('Location: ../../views/tenants/index.php');
exit;
