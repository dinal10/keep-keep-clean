<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_session_if_needed(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_admin(): ?array
{
    start_session_if_needed();
    db_bootstrap();

    $username = $_SESSION['admin_username'] ?? null;
    if (!is_string($username) || $username === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username FROM admin_users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        json_response(['message' => 'Unauthorized'], 401);
    }

    return $admin;
}

function login_admin(string $username, string $password): array
{
    start_session_if_needed();
    db_bootstrap();

    $stmt = db()->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        throw new InvalidArgumentException('Username atau password salah.');
    }

    session_regenerate_id(true);
    $_SESSION['admin_username'] = $user['username'];

    return [
        'id' => $user['id'],
        'username' => $user['username'],
    ];
}

function logout_admin(): void
{
    start_session_if_needed();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
