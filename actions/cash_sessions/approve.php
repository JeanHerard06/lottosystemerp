<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_permission($pdo, 'cash_sessions.approve');
require_post();
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
$decision = $_POST['decision'] ?? 'approved';
if (!in_array($decision, ['approved','rejected'], true)) { die('Décision invalide.'); }

$stmt = $pdo->prepare('SELECT * FROM cash_sessions WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($session ?: null, 'session caisse');
if ($session['status'] !== 'closed') { die('La session doit être fermée avant validation.'); }

$stmt = $pdo->prepare('UPDATE cash_sessions SET status=?, approved_by=?, approved_at=NOW() WHERE id=?');
$stmt->execute([$decision, current_user_id(), $id]);
audit_log($pdo, current_user_id(), strtoupper($decision) . '_CASH_SESSION', 'Validation session caisse #' . $id);
redirect('/views/cash_sessions/show.php?id=' . $id);
