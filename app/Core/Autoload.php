<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $base = dirname(__DIR__);
    $class = ltrim($class, '\\');
    $relative = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    $paths = [
        $base . DIRECTORY_SEPARATOR . $relative,
        $base . '/Core/' . basename($relative),
        $base . '/Contracts/' . basename($relative),
        $base . '/Repositories/' . basename($relative),
        $base . '/Services/' . basename($relative),
        $base . '/Middleware/' . basename($relative),
        $base . '/Controllers/' . basename($relative),
        $base . '/Models/' . basename($relative),
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});
