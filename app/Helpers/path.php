<?php

function app_path(string $path = ''): string
{
    return dirname(__DIR__) . ($path ? '/' . ltrim($path, '/') : '');
}

function base_path(string $path = ''): string
{
    return dirname(__DIR__, 2) . ($path ? '/' . ltrim($path, '/') : '');
}

function view_path(string $path = ''): string
{
    return base_path('views' . ($path ? '/' . ltrim($path, '/') : ''));
}
