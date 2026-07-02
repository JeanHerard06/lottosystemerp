<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/tenant.php';

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('current_user_id')) {
    function current_user_id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
}

if (!function_exists('current_user_role')) {
    function current_user_role(): string
    {
        return $_SESSION['role'] ?? 'guest';
    }
}

if (!function_exists('require_auth')) {
    function require_auth(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /views/login.php');
            exit;
        }
    }
}

function protected_role_slugs(): array
{
    return ['super_admin'];
}

function assignable_role_slugs(): array
{
    // super_admin is a protected platform role: it is never assignable from tenant screens.
    if (is_super_admin()) {
        return ['tenant_admin', 'admin', 'superviseur', 'agent'];
    }
    // Tenant users cannot create platform-level roles.
    return ['admin', 'superviseur', 'agent'];
}

function can_assign_role_slug(string $slug): bool
{
    if (in_array($slug, protected_role_slugs(), true)) {
        return false;
    }
    return in_array($slug, assignable_role_slugs(), true);
}

function normalize_system_role(string $role): string
{
    return can_assign_role_slug($role) ? $role : 'agent';
}

function allowed_role_ids(PDO $pdo): array
{
    $slugs = assignable_role_slugs();
    if (!$slugs) { return []; }
    $in = implode(',', array_fill(0, count($slugs), '?'));
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE slug IN ($in)");
    $stmt->execute($slugs);
    return array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id'));
}

function filter_allowed_role_ids(PDO $pdo, array $roleIds): array
{
    $allowed = allowed_role_ids($pdo);
    return array_values(array_intersect(array_map('intval', $roleIds), $allowed));
}

function assert_user_mutable(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) { http_response_code(404); die('Utilisateur introuvable.'); }

    if ((string)$user['role'] === 'super_admin' && !is_super_admin()) {
        http_response_code(403);
        die('Accès refusé: seul le super_admin peut gérer un autre super_admin.');
    }

    if (!is_super_admin() && (int)$user['tenant_id'] !== (int)current_tenant_id()) {
        http_response_code(403);
        die('Accès refusé: utilisateur hors tenant.');
    }

    return $user;
}



function protected_permission_slugs(): array
{
    return ['tenants.manage','subscriptions.manage','plans.manage','super_admin.manage','system.settings'];
}

function visible_permissions(PDO $pdo): array
{
    if (is_super_admin()) {
        return $pdo->query("SELECT * FROM permissions ORDER BY module, name")->fetchAll(PDO::FETCH_ASSOC);
    }
    $blocked = protected_permission_slugs();
    $in = implode(',', array_fill(0, count($blocked), '?'));
    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE slug NOT IN ($in) ORDER BY module, name");
    $stmt->execute($blocked);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function filter_permission_ids(PDO $pdo, array $permissionIds): array
{
    $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));
    if (!$permissionIds) { return []; }
    if (is_super_admin()) { return $permissionIds; }
    $allowed = array_map('intval', array_column(visible_permissions($pdo), 'id'));
    return array_values(array_intersect($permissionIds, $allowed));
}

function assert_role_mutable(PDO $pdo, int $roleId): array
{
    $stmt = $pdo->prepare('SELECT * FROM roles WHERE id=? LIMIT 1');
    $stmt->execute([$roleId]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$role) { http_response_code(404); die('Rôle introuvable.'); }
    if (in_array($role['slug'], protected_role_slugs(), true) && !is_super_admin()) {
        http_response_code(403); die('Accès refusé: rôle plateforme protégé.');
    }
    return $role;
}

function user_permissions(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("\n        SELECT DISTINCT p.slug\n        FROM permissions p\n        JOIN role_permissions rp ON rp.permission_id = p.id\n        JOIN user_roles ur ON ur.role_id = rp.role_id\n        WHERE ur.user_id = ?\n    ");
    $stmt->execute([$userId]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'slug');
}

function has_permission(PDO $pdo, string $permission): bool
{
    if (is_super_admin()) { return true; }
    $userId = current_user_id();
    if (!$userId) { return false; }
    return in_array($permission, user_permissions($pdo, $userId), true);
}

function require_permission(PDO $pdo, string $permission): void
{
    require_auth();
    require_tenant_active($pdo);
    if (!has_permission($pdo, $permission)) {
        http_response_code(403);
        die('Accès refusé: permission requise ' . e($permission));
    }
}
