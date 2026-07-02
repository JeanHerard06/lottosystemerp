<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/csrf.php';
require_once __DIR__ . '/../app/Helpers/security.php';
require_once __DIR__ . '/../app/Helpers/tenant.php';

require_post();
verify_csrf();

$username = input_string('username', 50);
$password = (string)($_POST['password'] ?? '');

$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND status = 1 LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    if (!tenant_is_active($pdo, isset($user['tenant_id']) ? (int)$user['tenant_id'] : null)) {
        redirect('../views/login.php?error=' . urlencode('Tenant suspendu ou abonnement expiré.'));
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['tenant_id'] = $user['tenant_id'] ?? null;
    redirect('../views/dashboard.php');
}

redirect('../views/login.php?error=' . urlencode('Identifiant ou mot de passe incorrect'));
