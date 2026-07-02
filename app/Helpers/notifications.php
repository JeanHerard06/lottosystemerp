<?php
require_once __DIR__ . '/tenant.php';

function notification_scope_clause(PDO $pdo, string $alias = 'n', string $prefix = 'WHERE'): array
{
    $clauses = [];
    $params = [];

    if (!is_super_admin()) {
        $clauses[] = $alias . '.tenant_id = ?';
        $params[] = current_tenant_id();
    }

    // User-specific notifications are visible only to that user.
    // Broadcast tenant notifications have user_id NULL.
    $clauses[] = '(' . $alias . '.user_id IS NULL OR ' . $alias . '.user_id = ?)';
    $params[] = current_user_id();

    if (!$clauses) { return ['', []]; }
    return [' ' . $prefix . ' ' . implode(' AND ', $clauses) . ' ', $params];
}

function unread_notifications_count(PDO $pdo): int
{
    if (empty($_SESSION['user_id'])) { return 0; }
    [$where, $params] = notification_scope_clause($pdo, 'n', 'WHERE');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications n $where AND n.read_at IS NULL");
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function recent_notifications(PDO $pdo, int $limit = 10): array
{
    [$where, $params] = notification_scope_clause($pdo, 'n', 'WHERE');
    $limit = max(1, min(50, $limit));
    $stmt = $pdo->prepare("SELECT n.* FROM notifications n $where ORDER BY n.id DESC LIMIT $limit");
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function create_notification(PDO $pdo, ?int $tenantId, ?int $userId, string $title, string $message, string $type = 'info', ?string $linkUrl = null, ?int $createdBy = null): int
{
    if (!in_array($type, ['info','success','warning','danger'], true)) { $type = 'info'; }
    $stmt = $pdo->prepare('INSERT INTO notifications (tenant_id, user_id, title, message, type, link_url, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$tenantId, $userId, $title, $message, $type, $linkUrl, $createdBy]);
    return (int)$pdo->lastInsertId();
}

function ensure_notification_scope(PDO $pdo, int $notificationId): array
{
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE id=? LIMIT 1');
    $stmt->execute([$notificationId]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$notification) { http_response_code(404); die('Notification introuvable.'); }

    if (!is_super_admin()) {
        if ((int)$notification['tenant_id'] !== (int)current_tenant_id()) {
            http_response_code(403); die('Accès refusé: notification hors tenant.');
        }
        if (!empty($notification['user_id']) && (int)$notification['user_id'] !== (int)current_user_id()) {
            http_response_code(403); die('Accès refusé: notification utilisateur.');
        }
    }
    return $notification;
}
