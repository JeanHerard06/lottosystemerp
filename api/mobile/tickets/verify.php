<?php
require_once __DIR__ . '/../auth.php';
$user = mobile_user($pdo);
$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { $payload = $_POST; }
$code = trim((string)($payload['code'] ?? ''));
if ($code === '') {
    mobile_json(['success' => false, 'valid' => false, 'message' => 'Code manquant'], 422);
}
$tenantId = !empty($user['tenant_id']) ? (int)$user['tenant_id'] : null;
$sql = "SELECT id, tenant_id, fiche_code, total_amount, gain_amount, status, created_at FROM fiches WHERE fiche_code = ?";
$params = [$code];
if ($tenantId) {
    $sql .= " AND tenant_id = ?";
    $params[] = $tenantId;
}
$sql .= " LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
try {
    $log = $pdo->prepare("INSERT INTO ticket_verification_logs(tenant_id, ticket_id, verification_code, checked_by, status, ip_address, device_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $log->execute([$tenantId ?: ($ticket['tenant_id'] ?? null), $ticket['id'] ?? null, $code, (int)$user['id'], $ticket ? 'valid' : 'not_found', $_SERVER['REMOTE_ADDR'] ?? null, $payload['device_id'] ?? null]);
} catch (Throwable $e) { /* optional table may not exist */ }
if (!$ticket) {
    mobile_json(['success' => true, 'valid' => false, 'message' => 'Ticket introuvable ou hors tenant.']);
}
mobile_json(['success' => true, 'valid' => true, 'message' => 'Ticket valide', 'ticket' => $ticket]);
