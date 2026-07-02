<?php
session_start();
require "../../config/database.php";
require_once "../../app/Helpers/security.php";
require_once "../../app/Helpers/csrf.php";
require_once "../../includes/auth.php";
require_once "../../app/Helpers/tenant.php";
require_once "../../app/Helpers/audit.php";

require_auth();
require_tenant_active($pdo);
require_post();
verify_csrf();

$userId = current_user_id();
$current = (string)($_POST['current_password'] ?? '');
$new = (string)($_POST['new_password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');

if (strlen($new) < 8) {
    redirect('../../views/users/change_password.php?error=' . urlencode('Le nouveau mot de passe doit contenir au moins 8 caractères.'));
}
if ($new !== $confirm) {
    redirect('../../views/users/change_password.php?error=' . urlencode('La confirmation ne correspond pas.'));
}
if ($current === $new) {
    redirect('../../views/users/change_password.php?error=' . urlencode('Le nouveau mot de passe doit être différent de l’ancien.'));
}

$stmt = $pdo->prepare('SELECT id, password FROM users WHERE id = ? AND status = 1 LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($current, $user['password'])) {
    audit_log($pdo, $userId, 'PASSWORD_CHANGE_FAILED', 'Mot de passe actuel incorrect.');
    redirect('../../views/users/change_password.php?error=' . urlencode('Mot de passe actuel incorrect.'));
}

$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);

audit_log($pdo, $userId, 'PASSWORD_CHANGED', 'Utilisateur a changé son mot de passe.');
redirect('../../views/users/change_password.php?success=1');
