<?php

declare(strict_types=1);

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../http.php';

handle_api(function (): void {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        require_admin();
        json_response(['site' => site_payload()]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['message' => 'Method not allowed'], 405);
    }

    require_admin();
    $input = json_input();
    $site = $input['site'] ?? null;
    if (!is_array($site)) {
        throw new InvalidArgumentException('Payload site tidak valid.');
    }

    save_site_payload($site);
    json_response(['success' => true, 'site' => site_payload()]);
});

