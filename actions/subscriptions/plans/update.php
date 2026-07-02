<?php
require "../../../config/database.php";
require_once "../../../includes/auth.php";
require_once "../../../app/Helpers/permissions.php";
require_once "../../../app/Helpers/csrf.php";
require_once "../../../app/Helpers/security.php";
require_once "../../../app/Helpers/audit.php";
require_permission($pdo,'plans.manage'); require_post(); verify_csrf();
$id=(int)($_POST['id']??0); $code=strtolower(preg_replace('/[^a-z0-9_-]+/','-',input_string('code',50))); $name=input_string('name',100);
$pm=input_money('price_monthly'); $py=input_money('price_yearly'); $maxAgents=($_POST['max_agents']??'')===''?null:(int)$_POST['max_agents']; $maxAgencies=($_POST['max_agencies']??'')===''?null:(int)$_POST['max_agencies']; $features=input_string('features',1000,false); $status=in_array($_POST['status']??'active',['active','inactive'],true)?$_POST['status']:'active';
$stmt=$pdo->prepare("UPDATE subscription_plans SET code=?,name=?,price_monthly=?,price_yearly=?,max_agents=?,max_agencies=?,features=?,status=? WHERE id=?");
$stmt->execute([$code,$name,$pm,$py,$maxAgents,$maxAgencies,$features,$status,$id]);
audit_log($pdo,current_user_id(),'PLAN_UPDATE','Plan SaaS modifié: '.$name);
header('Location: ../../../views/subscriptions/plans/index.php'); exit;
