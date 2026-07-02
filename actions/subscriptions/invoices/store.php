<?php
require "../../../config/database.php";
require_once "../../../includes/auth.php";
require_once "../../../app/Helpers/permissions.php";
require_once "../../../app/Helpers/csrf.php";
require_once "../../../app/Helpers/security.php";
require_once "../../../app/Helpers/audit.php";
require_permission($pdo,'subscriptions.manage'); require_post(); verify_csrf();
$tenantId=(int)($_POST['tenant_id']??0); $total=input_money('total_amount'); $periodStart=$_POST['period_start']?:null; $periodEnd=$_POST['period_end']?:null; $dueDate=$_POST['due_date']?:null; $notes=input_string('notes',1000,false);
$invoiceNo='INV-'.date('Ymd-His').'-'.random_int(100,999);
$stmt=$pdo->prepare("SELECT id FROM tenant_subscriptions WHERE tenant_id=? AND status IN ('trial','active','past_due') ORDER BY id DESC LIMIT 1"); $stmt->execute([$tenantId]); $subId=$stmt->fetchColumn() ?: null;
$stmt=$pdo->prepare("INSERT INTO subscription_invoices(tenant_id,subscription_id,invoice_no,period_start,period_end,total_amount,due_date,notes,created_by) VALUES(?,?,?,?,?,?,?,?,?)");
$stmt->execute([$tenantId,$subId,$invoiceNo,$periodStart,$periodEnd,$total,$dueDate,$notes,current_user_id()]);
audit_log($pdo,current_user_id(),'INVOICE_CREATE','Facture créée: '.$invoiceNo);
header('Location: ../../../views/subscriptions/invoices/index.php'); exit;
