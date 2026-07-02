<?php
require_once __DIR__ . '/../../config/database.php'; require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../app/Helpers/tenant.php'; require_once __DIR__ . '/../../app/Helpers/csrf.php'; require_once __DIR__ . '/../../app/Helpers/security.php'; require_once __DIR__ . '/../../app/Helpers/audit.php';
require_super_admin(); require_post(); verify_csrf();
$id=(int)($_POST['id']??0); $username=input_string('admin_username',50); $password=(string)($_POST['admin_password']??'tenant123'); $expires=$_POST['expires_at'] ?: null;
$stmt=$pdo->prepare("SELECT * FROM tenant_registrations WHERE id=? AND status='pending'"); $stmt->execute([$id]); $r=$stmt->fetch(PDO::FETCH_ASSOC); if(!$r){die('Demande invalide.');}
$slug=strtolower(trim(preg_replace('/[^a-z0-9-]+/','-', $r['business_name']),'-')); if(!$slug) $slug='tenant-'.$id;
$pdo->beginTransaction();
$baseSlug=$slug; $i=1; while(true){ $st=$pdo->prepare('SELECT COUNT(*) FROM tenants WHERE slug=?'); $st->execute([$slug]); if(!$st->fetchColumn()) break; $slug=$baseSlug.'-'.$i++; }
$stmt=$pdo->prepare("INSERT INTO tenants(name,slug,plan,status,expires_at,notes) VALUES(?,?,?,?,?,?)"); $stmt->execute([$r['business_name'],$slug,$r['requested_plan'],'active',$expires,'Created from tenant registration #'.$id]); $tenantId=(int)$pdo->lastInsertId();
$stmt=$pdo->prepare("INSERT INTO tenant_settings(tenant_id,setting_key,setting_value) VALUES(?,?,?),(?,?,?)"); $stmt->execute([$tenantId,'owner_name',$r['owner_name'],$tenantId,'contact_email',$r['email']]);
$stmt=$pdo->prepare("INSERT INTO tenant_subscriptions(tenant_id,plan,status,starts_at,ends_at,amount) VALUES(?,?, 'trial', CURDATE(), ?, 0)"); $stmt->execute([$tenantId,$r['requested_plan'],$expires]);
$stmt=$pdo->prepare("INSERT INTO users(tenant_id,name,username,password,role,status) VALUES(?,?,?,?, 'tenant_admin', 1)"); $stmt->execute([$tenantId,$r['owner_name'],$username,password_hash($password,PASSWORD_DEFAULT)]); $userId=(int)$pdo->lastInsertId();
$stmt=$pdo->prepare("INSERT IGNORE INTO roles(name,slug) VALUES('Tenant Admin','tenant_admin')"); $stmt->execute();
$stmt=$pdo->prepare("INSERT IGNORE INTO user_roles(user_id,role_id) SELECT ?, id FROM roles WHERE slug='tenant_admin'"); $stmt->execute([$userId]);
$stmt=$pdo->prepare("UPDATE tenant_registrations SET status='approved', tenant_id=?, approved_by=?, approved_at=NOW(), admin_username=? WHERE id=?"); $stmt->execute([$tenantId,current_user_id(),$username,$id]);
audit_log($pdo,current_user_id(),'TENANT_REGISTRATION_APPROVE','Approved tenant registration #'.$id.' tenant_id='.$tenantId);
$pdo->commit(); header('Location: ../../views/tenant_registrations/show.php?id='.$id); exit;
