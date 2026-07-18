<?php
//function e($value): string
//{
//    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
//}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die('Méthode non autorisée');
    }
}

function input_string(string $key, int $max = 255, bool $required = true): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    if ($required && $value === '') {
        die('Champ obligatoire manquant: ' . e($key));
    }
    return mb_substr($value, 0, $max);
}

function input_money(string $key, bool $required = true): float
{
    $raw = trim((string)($_POST[$key] ?? ''));
    if ($required && $raw === '') {
        die('Montant obligatoire manquant: ' . e($key));
    }
    $value = (float)$raw;
    if ($value < 0) {
        die('Montant invalide');
    }
    return $value;
}

/**
 * Send conservative security headers that do not break the existing Tailwind CDN
 * or inline scripts. Call before any output.
 */
function send_security_headers(bool $authenticated = true): void
{
    if (headers_sent()) { return; }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(self), microphone=(), geolocation=(self), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    if ($authenticated) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }
}

function input_int(string $key, bool $required = true, int $min = 0): ?int
{
    $raw = $_POST[$key] ?? null;
    if (($raw === null || $raw === '') && !$required) { return null; }
    if ($raw === null || $raw === '' || filter_var($raw, FILTER_VALIDATE_INT) === false) {
        http_response_code(422);
        die('Valeur entière invalide: ' . e($key));
    }
    $value = (int)$raw;
    if ($value < $min) {
        http_response_code(422);
        die('Valeur hors limite: ' . e($key));
    }
    return $value;
}

function input_enum(string $key, array $allowed, string $default = ''): string
{
    $value = trim((string)($_POST[$key] ?? $default));
    if (!in_array($value, $allowed, true)) {
        http_response_code(422);
        die('Valeur invalide: ' . e($key));
    }
    return $value;
}

