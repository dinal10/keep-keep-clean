<?php

declare(strict_types=1);

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../http.php';

handle_api(function (): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['message' => 'Method not allowed'], 405);
    }

    $admin = require_admin();
    $input = json_input();

    $currentPassword = (string) ($input['current_password'] ?? '');
    $newPassword = (string) ($input['new_password'] ?? '');
    $confirmPassword = (string) ($input['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        throw new InvalidArgumentException('Semua field password wajib diisi.');
    }

    if ($newPassword !== $confirmPassword) {
        throw new InvalidArgumentException('Konfirmasi password baru tidak sama.');
    }

    if (strlen($newPassword) < 8) {
        throw new InvalidArgumentException('Password baru minimal 8 karakter.');
    }

    $stmt = db()->prepare('SELECT password_hash FROM admin_users WHERE id = :id');
    $stmt->execute(['id' => $admin['id']]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($currentPassword, (string) $row['password_hash'])) {
        throw new InvalidArgumentException('Password lama tidak cocok.');
    }

    $update = db()->prepare('UPDATE admin_users SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $update->execute([
        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        'id' => $admin['id'],
    ]);

    json_response(['success' => true]);
});

