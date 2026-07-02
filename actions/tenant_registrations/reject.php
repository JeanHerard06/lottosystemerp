<?php
require_once __DIR__ . '/../../config/database.php'; require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../app/Helpers/tenant.php'; require_once __DIR__ . '/../../app/Helpers/csrf.php'; require_once __DIR__ . '/../../app/Helpers/security.php'; require_once __DIR__ . '/../../app/Helpers/audit.php';
require_super_admin(); require_post(); verify_csrf();
$id=(int)($_POST['id']??0); $reason=input_string('reason',1000,false);
$stmt=$pdo->prepare("UPDATE tenant_registrations SET status='rejected', rejected_by=?, rejected_at=NOW(), rejection_reason=? WHERE id=? AND status='pending'"); $stmt->execute([current_user_id(),$reason,$id]);
audit_log($pdo,current_user_id(),'TENANT_REGISTRATION_REJECT','Rejected tenant registration #'.$id);
header('Location: ../../views/tenant_registrations/index.php'); exit;
