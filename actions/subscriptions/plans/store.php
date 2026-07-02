<?php
require "../../../config/database.php";
require_once "../../../includes/auth.php";
require_once "../../../app/Helpers/permissions.php";
require_once "../../../app/Helpers/csrf.php";
require_once "../../../app/Helpers/security.php";
require_once "../../../app/Helpers/audit.php";
require_permission($pdo,'plans.manage'); require_post(); verify_csrf();
$code=strtolower(preg_replace('/[^a-z0-9_-]+/','-',input_string('code',50)));
$name=input_string('name',100); $pm=input_money('price_monthly'); $py=input_money('price_yearly');
$maxAgents=($_POST['max_agents']??'')===''?null:(int)$_POST['max_agents']; $maxAgencies=($_POST['max_agencies']??'')===''?null:(int)$_POST['max_agencies']; $features=input_string('features',1000,false);
$stmt=$pdo->prepare("INSERT INTO subscription_plans(code,name,price_monthly,price_yearly,max_agents,max_agencies,features) VALUES(?,?,?,?,?,?,?)");
$stmt->execute([$code,$name,$pm,$py,$maxAgents,$maxAgencies,$features]);
audit_log($pdo,current_user_id(),'PLAN_CREATE','Plan SaaS créé: '.$name);
header('Location: ../../../views/subscriptions/plans/index.php'); exit;
