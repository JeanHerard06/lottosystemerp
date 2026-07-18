<?php

declare(strict_types=1);

final class Application
{
    private Container $container;
    private Router $router;
    private EventDispatcher $events;
    private Config $config;

    public function __construct(private string $basePath)
    {
        $this->container = new Container();
        $this->router = new Router();
        $this->events = new EventDispatcher();
        $this->config = new Config();

        $this->container->instance(self::class, $this);
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(Router::class, $this->router);
        $this->container->instance(EventDispatcher::class, $this->events);
        $this->container->instance(Config::class, $this->config);
        $this->container->singleton(Logger::class, fn (): Logger => new Logger($this->storagePath('logs/application.log')));
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function events(): EventDispatcher
    {
        return $this->events;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function basePath(string $path = ''): string
    {
        return rtrim($this->basePath, DIRECTORY_SEPARATOR)
            . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage' . ($path !== '' ? DIRECTORY_SEPARATOR . $path : ''));
    }

    public function run(?Request $request = null): mixed
    {
        $request ??= Request::capture();
        $this->container->instance(Request::class, $request);
        return $this->router->dispatch($request, $this->container);
    }
}
