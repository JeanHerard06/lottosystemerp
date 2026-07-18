<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/Contracts/ContainerInterface.php';

final class Container implements ContainerInterface
{
    /** @var array<string, array{concrete: callable|string, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $resolving = [];

    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => false];
        unset($this->instances[$abstract]);
    }

    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => true];
        unset($this->instances[$abstract]);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract])
            || isset($this->bindings[$abstract])
            || class_exists($abstract);
    }

    public function make(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->resolving[$abstract])) {
            throw new RuntimeException('Circular dependency detected while resolving: ' . $abstract);
        }

        $this->resolving[$abstract] = true;

        try {
            $binding = $this->bindings[$abstract] ?? ['concrete' => $abstract, 'shared' => false];
            $object = $this->build($binding['concrete']);

            if ($binding['shared']) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        } finally {
            unset($this->resolving[$abstract]);
        }
    }

    private function build(callable|string $concrete): object
    {
        if (is_callable($concrete)) {
            $object = $concrete($this);
            if (!is_object($object)) {
                throw new RuntimeException('Container factory must return an object.');
            }
            return $object;
        }

        if (!class_exists($concrete)) {
            throw new RuntimeException('Class not found: ' . $concrete);
        }

        $reflection = new ReflectionClass($concrete);
        if (!$reflection->isInstantiable()) {
            throw new RuntimeException('Class is not instantiable: ' . $concrete);
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(sprintf(
                'Unable to resolve parameter $%s for %s.',
                $parameter->getName(),
                $concrete
            ));
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
