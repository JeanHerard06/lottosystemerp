<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'app/Core/Application.php',
    'app/Core/Container.php',
    'app/Core/Config.php',
    'app/Core/Request.php',
    'app/Core/Response.php',
    'app/Core/Router.php',
    'app/Core/EventDispatcher.php',
    'app/Core/Logger.php',
    'app/Contracts/ContainerInterface.php',
    'bootstrap/app.php',
];

$errors = [];
foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing: ' . $file;
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

require_once $root . '/app/Core/Autoload.php';

$app = require $root . '/bootstrap/app.php';
if (!$app instanceof Application) {
    fwrite(STDERR, "Bootstrap did not return Application.\n");
    exit(1);
}

$container = $app->container();
$container->singleton(stdClass::class, static fn (): stdClass => new stdClass());
if ($container->make(stdClass::class) !== $container->make(stdClass::class)) {
    fwrite(STDERR, "Singleton resolution failed.\n");
    exit(1);
}

$app->events()->listen('qa.event', static function (mixed $payload): void {
    if ($payload !== 'ok') {
        throw new RuntimeException('Unexpected event payload.');
    }
});
$app->events()->dispatch('qa.event', 'ok');

$app->router()->get('/health-core', static fn (): array => ['status' => 'ok']);
$request = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/health-core']);
$result = $app->router()->dispatch($request, $container);
if (($result['status'] ?? null) !== 'ok') {
    fwrite(STDERR, "Router dispatch failed.\n");
    exit(1);
}

echo "Core architecture check passed.\n";
