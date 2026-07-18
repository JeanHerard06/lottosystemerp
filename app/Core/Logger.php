<?php

declare(strict_types=1);

final class Logger
{
    public function __construct(private string $file)
    {
    }

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    /** @param array<string, mixed> $context */
    private function write(string $level, string $message, array $context): void
    {
        $directory = dirname($this->file);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create log directory: ' . $directory);
        }

        $record = [
            'timestamp' => date(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'context' => $this->sanitize($context),
        ];

        file_put_contents(
            $this->file,
            json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /** @param array<string, mixed> $context
     *  @return array<string, mixed>
     */
    private function sanitize(array $context): array
    {
        $blocked = ['password', 'password_confirmation', 'token', 'api_key', 'authorization'];
        foreach ($context as $key => $value) {
            if (in_array(strtolower((string)$key), $blocked, true)) {
                $context[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $context[$key] = $this->sanitize($value);
            }
        }
        return $context;
    }
}
