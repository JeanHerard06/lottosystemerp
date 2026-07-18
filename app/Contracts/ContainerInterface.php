<?php

declare(strict_types=1);

interface ContainerInterface
{
    public function bind(string $abstract, callable|string $concrete): void;
    public function singleton(string $abstract, callable|string $concrete): void;
    public function instance(string $abstract, object $instance): void;
    public function has(string $abstract): bool;
    public function make(string $abstract): object;
}
