<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function upload_directory_for_type(string $type): string
{
    return match ($type) {
        'gallery', 'poster' => 'uploads/gallery',
        'video' => 'uploads/videos',
        'branding' => 'uploads/branding',
        default => throw new InvalidArgumentException('Tipe upload tidak didukung.'),
    };
}

function allowed_extensions_for_type(string $type): array
{
    return match ($type) {
        'gallery', 'poster', 'branding' => ['jpg', 'jpeg', 'png', 'webp', 'svg'],
        'video' => ['mp4', 'webm', 'mov'],
        default => throw new InvalidArgumentException('Tipe upload tidak didukung.'),
    };
}

function store_uploaded_file(array $file, string $type): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload file gagal.');
    }

    $originalName = (string) ($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = allowed_extensions_for_type($type);
    if (!in_array($extension, $allowed, true)) {
        throw new InvalidArgumentException('Format file tidak diizinkan.');
    }

    $relativeDir = upload_directory_for_type($type);
    $absoluteDir = base_path($relativeDir);
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0777, true) && !is_dir($absoluteDir)) {
        throw new RuntimeException('Gagal membuat folder upload.');
    }

    $filename = sprintf('%s-%s-%s.%s', $type, date('YmdHis'), bin2hex(random_bytes(4)), $extension);
    $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file((string) $file['tmp_name'], $absolutePath)) {
        throw new RuntimeException('Gagal menyimpan file upload.');
    }

    return str_replace('\\', '/', $relativeDir . '/' . $filename);
}

