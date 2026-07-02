<?php

class Response
{
    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    public static function abort(int $status, string $message): void
    {
        http_response_code($status);
        die($message);
    }
}
