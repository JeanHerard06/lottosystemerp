<?php

spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__);
    $paths = [
        $base . '/Core/' . $class . '.php',
        $base . '/Repositories/' . $class . '.php',
        $base . '/Services/' . $class . '.php',
        $base . '/Middleware/' . $class . '.php',
        $base . '/Controllers/' . $class . '.php',
        $base . '/Models/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});
