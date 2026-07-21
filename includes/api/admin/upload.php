<?php

declare(strict_types=1);

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../http.php';
require_once __DIR__ . '/../../media.php';

handle_api(function (): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['message' => 'Method not allowed'], 405);
    }

    require_admin();
    $type = trim((string) ($_POST['type'] ?? ''));
    if ($type === '') {
        throw new InvalidArgumentException('Tipe upload wajib diisi.');
    }

    if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
        throw new InvalidArgumentException('File upload tidak ditemukan.');
    }

    $path = store_uploaded_file($_FILES['file'], $type);
    json_response(['path' => $path]);
});
