<?php

declare(strict_types=1);

final class EventDispatcher
{
    /** @var array<string, list<callable>> */
    private array $listeners = [];

    public function listen(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(string $eventName, mixed $payload = null): void
    {
        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            $listener($payload, $eventName, $this);
        }
    }

    public function hasListeners(string $eventName): bool
    {
        return !empty($this->listeners[$eventName]);
    }
}
