<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../app/Helpers/tenant.php';

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

function require_role(array $roles): void
{
    require_auth();
    if (isset($GLOBALS['pdo'])) { require_tenant_active($GLOBALS['pdo']); }
    if (!in_array(current_user_role(), $roles, true)) {
        http_response_code(403);
        die('Accès refusé.');
    }
}

require_auth();
if (isset($GLOBALS['pdo'])) { require_tenant_active($GLOBALS['pdo']); }
