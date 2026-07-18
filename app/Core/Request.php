<?php

declare(strict_types=1);

final class Request
{
    /** @param array<string, mixed> $query
     *  @param array<string, mixed> $body
     *  @param array<string, mixed> $server
     *  @param array<string, mixed> $files
     *  @param array<string, mixed> $cookies
     */
    public function __construct(
        private array $query = [],
        private array $body = [],
        private array $server = [],
        private array $files = [],
        private array $cookies = []
    ) {
    }

    public static function capture(): self
    {
        $body = $_POST;
        $contentType = (string)($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains(strtolower($contentType), 'application/json')) {
            $decoded = json_decode((string)file_get_contents('php://input'), true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return new self($_GET, $body, $_SERVER, $_FILES, $_COOKIE);
    }

    public function method(): string
    {
        return strtoupper((string)($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function path(): string
    {
        $uri = (string)($this->server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        return '/' . ltrim(is_string($path) ? $path : '/', '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$normalized] ?? $default;
    }

    public function ip(): string
    {
        return (string)($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function isJson(): bool
    {
        return str_contains(strtolower((string)$this->header('Content-Type', '')), 'application/json');
    }
}
