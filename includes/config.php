<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__);
    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function env_load(string $file): array
{
    static $loaded = null;

    if ($loaded !== null) {
        return $loaded;
    }

    $values = [];
    if (!is_file($file)) {
        return $loaded = $values;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && (($value[0] === "'" && str_ends_with($value, "'")) || ($value[0] === '"' && str_ends_with($value, '"')))) {
            $value = substr($value, 1, -1);
        }

        $values[$key] = $value;
    }

    return $loaded = $values;
}

function app_env(string $key, ?string $default = null): ?string
{
    $env = env_load(base_path('.env'));
    return $env[$key] ?? $default;
}

