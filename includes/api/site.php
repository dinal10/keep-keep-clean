<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../http.php';

handle_api(function (): void {
    json_response(site_payload());
});

