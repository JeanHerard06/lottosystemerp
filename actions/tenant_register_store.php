<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../app/Helpers/security.php';
require_once __DIR__ . '/../app/Helpers/audit.php';
require_post();
verify_csrf();
$business = input_string('business_name', 150);
$owner = input_string('owner_name', 120);
$email = input_string('email', 150);
$phone = input_string('phone', 50);
$address = input_string('address', 255, false);
$plan = $_POST['plan'] ?? 'basic';
if (!in_array($plan, ['basic','pro','enterprise'], true)) { $plan = 'basic'; }
$notes = input_string('notes', 1000, false);
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tenant_registrations WHERE email=? AND status='pending'");
$stmt->execute([$email]);
if ($stmt->fetchColumn() > 0) { redirect('../views/tenant_register.php?error=' . urlencode('Une demande est déjà en attente avec cet email.')); }
$stmt = $pdo->prepare("INSERT INTO tenant_registrations(business_name, owner_name, email, phone, address, requested_plan, notes, status) VALUES(?,?,?,?,?,?,?,'pending')");
$stmt->execute([$business,$owner,$email,$phone,$address,$plan,$notes]);
redirect('../views/tenant_register.php?success=' . urlencode('Demande envoyée. Un super_admin doit approuver votre compte.'));
