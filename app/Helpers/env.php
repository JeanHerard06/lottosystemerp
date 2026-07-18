<?php
/**
 * Lightweight .env loader for local PHP installs without Composer.
 */
if (!function_exists('lotto_load_env')) {
    function lotto_load_env(?string $path = null): void
    {
        $path = $path ?: dirname(__DIR__, 2) . '/.env';
        if (!is_readable($path)) {
            return;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

if (!function_exists('env_value')) {
    function env_value(string $key, $default = null)
    {
        $value = getenv($key);
        if ($value === false && array_key_exists($key, $_ENV)) {
            $value = $_ENV[$key];
        }
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        $lower = strtolower((string)$value);
        if ($lower === 'true') { return true; }
        if ($lower === 'false') { return false; }
        if ($lower === 'null') { return null; }
        return $value;
    }
}

lotto_load_env();
