<?php
require_once __DIR__ . '/tenant.php';

function audit_log(PDO $pdo, ?int $userId, string $action, string $description, ?int $tenantId = null): void
{
    $tenantId = $tenantId ?? (function_exists('current_tenant_id') ? current_tenant_id() : null);

    $stmt = $pdo->prepare('INSERT INTO audit_logs (tenant_id, user_id, action_type, description) VALUES (?, ?, ?, ?)');
    $stmt->execute([$tenantId, $userId, $action, $description]);
}

function audit_logs_query(PDO $pdo, array $filters = []): array
{
    $where = [];
    $params = [];

    if (!is_super_admin()) {
        $where[] = 'al.tenant_id = ?';
        $params[] = current_tenant_id();
    } elseif (!empty($filters['tenant_id'])) {
        $where[] = 'al.tenant_id = ?';
        $params[] = (int)$filters['tenant_id'];
    }

    if (!empty($filters['user_id'])) {
        $where[] = 'al.user_id = ?';
        $params[] = (int)$filters['user_id'];
    }

    if (!empty($filters['action_type'])) {
        $where[] = 'al.action_type LIKE ?';
        $params[] = '%' . trim((string)$filters['action_type']) . '%';
    }

    if (!empty($filters['date_from'])) {
        $where[] = 'DATE(al.created_at) >= ?';
        $params[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $where[] = 'DATE(al.created_at) <= ?';
        $params[] = $filters['date_to'];
    }

    $sql = "
        SELECT al.*, u.name AS user_name, u.username, t.name AS tenant_name
        FROM audit_logs al
        LEFT JOIN users u ON u.id = al.user_id
        LEFT JOIN tenants t ON t.id = al.tenant_id
    ";

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY al.id DESC LIMIT 500';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
