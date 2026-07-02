<?php
require "../../../config/database.php";
require_once "../../../includes/auth.php";
require_once "../../../app/Helpers/permissions.php";
require_once "../../../app/Helpers/csrf.php";
require_once "../../../app/Helpers/security.php";
require_once "../../../app/Helpers/audit.php";
require_once "../../../app/Helpers/subscriptions.php";
require_permission($pdo,'payments.create'); require_post(); verify_csrf();
$invoiceId=(int)($_POST['invoice_id']??0); $methodId=($_POST['payment_method_id']??'')===''?null:(int)$_POST['payment_method_id']; $amount=input_money('amount');
$paidAt=($_POST['paid_at']??'') ? str_replace('T',' ',$_POST['paid_at']).':00' : date('Y-m-d H:i:s'); $ref=input_string('reference_no',100,false); $notes=input_string('notes',1000,false);
$stmt=$pdo->prepare("SELECT * FROM subscription_invoices WHERE id=?"); $stmt->execute([$invoiceId]); $invoice=$stmt->fetch(PDO::FETCH_ASSOC); if(!$invoice){ die('Facture introuvable'); }
$pdo->beginTransaction();
$stmt=$pdo->prepare("INSERT INTO subscription_payments(tenant_id,invoice_id,payment_method_id,amount,paid_at,reference_no,notes,created_by) VALUES(?,?,?,?,?,?,?,?)");
$stmt->execute([(int)$invoice['tenant_id'],$invoiceId,$methodId,$amount,$paidAt,$ref,$notes,current_user_id()]);
$stmt=$pdo->prepare("UPDATE subscription_invoices SET paid_amount = paid_amount + ? WHERE id=?"); $stmt->execute([$amount,$invoiceId]);
refresh_invoice_status($pdo,$invoiceId);
$stmt=$pdo->prepare("SELECT status, period_end FROM subscription_invoices WHERE id=?"); $stmt->execute([$invoiceId]); $newInv=$stmt->fetch(PDO::FETCH_ASSOC);
if (($newInv['status'] ?? '') === 'paid') {
    $end = $newInv['period_end'] ?: date('Y-m-d', strtotime('+1 month'));
    $stmt=$pdo->prepare("UPDATE tenants SET status='active', expires_at=GREATEST(COALESCE(expires_at, CURDATE()), ?) WHERE id=?");
    $stmt->execute([$end,(int)$invoice['tenant_id']]);
    $stmt=$pdo->prepare("UPDATE tenant_subscriptions SET status='active', ends_at=? WHERE tenant_id=? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$end,(int)$invoice['tenant_id']]);
}
audit_log($pdo,current_user_id(),'PAYMENT_CREATE','Paiement abonnement enregistré: '.$amount);
$pdo->commit();
header('Location: ../../../views/subscriptions/payments/index.php'); exit;
