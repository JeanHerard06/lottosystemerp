<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/security.php';
require_once __DIR__ . '/../../app/Helpers/csrf.php';
require_once __DIR__ . '/../../app/Helpers/audit.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../app/Helpers/permissions.php';
require_once __DIR__ . '/../../app/Helpers/tenant.php';

require_post();
verify_csrf();
require_permission($pdo, 'supervisors.manage');

$id = (int)($_POST['id'] ?? 0);
$userId = (int)($_POST['user_id'] ?? 0);
$name = input_string('name', 100);
$username = input_string('username', 50);
$agencyId = (int)($_POST['agency_id'] ?? 0);
$agency = ensure_agency_scope($pdo, $agencyId);
$status = (int)($_POST['status'] ?? 1) === 1 ? 1 : 0;
$passwordRaw = trim((string)($_POST['password'] ?? ''));

$stmt = $pdo->prepare('SELECT * FROM supervisors WHERE id=? AND user_id=? LIMIT 1');
$stmt->execute([$id, $userId]);
$supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
ensure_record_tenant($supervisor ?: null, 'superviseur');
if ((int)$agency['tenant_id'] !== (int)$supervisor['tenant_id']) { die('Agence hors tenant du superviseur.'); }

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username=? AND id<>?');
$stmt->execute([$username, $userId]);
if ((int)$stmt->fetchColumn() > 0) { die('Cet identifiant existe déjà.'); }

try {
    $pdo->beginTransaction();
    if ($passwordRaw !== '') {
        if (strlen($passwordRaw) < 6) { die('Mot de passe trop court.'); }
        $stmt = $pdo->prepare('UPDATE users SET name=?, username=?, status=?, password=? WHERE id=? AND tenant_id=?');
        $stmt->execute([$name, $username, $status, password_hash($passwordRaw, PASSWORD_DEFAULT), $userId, (int)$supervisor['tenant_id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name=?, username=?, status=? WHERE id=? AND tenant_id=?');
        $stmt->execute([$name, $username, $status, $userId, (int)$supervisor['tenant_id']]);
    }
    $stmt = $pdo->prepare('UPDATE supervisors SET agency_id=? WHERE id=? AND tenant_id=?');
    $stmt->execute([$agencyId, $id, (int)$supervisor['tenant_id']]);
    audit_log($pdo, (int)$_SESSION['user_id'], 'UPDATE_SUPERVISOR', 'Superviseur modifié: ' . $username . ' tenant #' . (int)$supervisor['tenant_id']);
    $pdo->commit();
    redirect('../../views/supervisors/index.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    die('Erreur modification superviseur: ' . e($e->getMessage()));
}
