<?php
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

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
