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

$name = input_string('name', 120);
$slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', input_string('slug', 80)));
$plan = $_POST['plan'] ?? 'basic';
if (!in_array($plan, ['basic','pro','enterprise'], true)) { $plan = 'basic'; }
$expiresAt = $_POST['expires_at'] ?: null;
$notes = input_string('notes', 500, false);

$stmt = $pdo->prepare("INSERT INTO tenants(name, slug, plan, status, expires_at, notes) VALUES (?, ?, ?, 'active', ?, ?)");
$stmt->execute([$name, $slug, $plan, $expiresAt, $notes]);
audit_log($pdo, current_user_id(), 'TENANT_CREATE', 'Tenant créé: ' . $name);
header('Location: ../../views/tenants/index.php');
exit;
