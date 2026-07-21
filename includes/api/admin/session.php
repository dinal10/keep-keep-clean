<?php

declare(strict_types=1);

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../http.php';

handle_api(function (): void {
    $admin = current_admin();
    json_response(['authenticated' => (bool) $admin, 'admin' => $admin]);
});

