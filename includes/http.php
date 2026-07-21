<?php

declare(strict_types=1);

function json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Body JSON tidak valid.');
    }

    return $decoded;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function handle_api(callable $handler): void
{
    try {
        $handler();
    } catch (Throwable $error) {
        $status = $error instanceof InvalidArgumentException ? 422 : 500;
        json_response(['message' => $error->getMessage()], $status);
    }
}

