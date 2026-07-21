<?php

declare(strict_types=1);

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../http.php';

handle_api(function (): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['message' => 'Method not allowed'], 405);
    }

    $input = json_input();
    $username = trim((string) ($input['username'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    if ($username === '' || $password === '') {
        throw new InvalidArgumentException('Username dan password wajib diisi.');
    }

    $admin = login_admin($username, $password);
    json_response(['admin' => $admin]);
});

