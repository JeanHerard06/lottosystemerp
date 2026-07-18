<?php

declare(strict_types=1);

final class Router
{
    /** @var array<string, array<string, callable|array{0:string,1:string}>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable|array $handler): void
    {
        $this->routes[strtoupper($method)][$this->normalize($path)] = $handler;
    }

    public function dispatch(Request $request, Container $container): mixed
    {
        $method = $request->method();
        $path = $this->normalize($request->path());
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            Response::abort(404, 'Route not found.');
        }

        if (is_array($handler)) {
            [$controller, $action] = $handler;
            $instance = $container->make($controller);
            return $instance->{$action}($request);
        }

        return $handler($request, $container);
    }

    /** @return array<string, array<string, callable|array{0:string,1:string}>> */
    public function routes(): array
    {
        return $this->routes;
    }

    private function normalize(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }
}
