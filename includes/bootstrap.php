<?php
declare(strict_types=1);

session_start();

const DATA_FILE = __DIR__ . '/../data/site.json';
const USERS_FILE = __DIR__ . '/../data/users.json';
const UPLOAD_GALLERY_DIR = __DIR__ . '/../uploads/gallery';
const UPLOAD_BRANDING_DIR = __DIR__ . '/../uploads/branding';
const UPLOAD_VIDEO_DIR = __DIR__ . '/../uploads/videos';

date_default_timezone_set('Asia/Jakarta');

function ensure_directories(): void
{
    foreach ([UPLOAD_GALLERY_DIR, UPLOAD_BRANDING_DIR, UPLOAD_VIDEO_DIR] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}

function load_site_data(): array
{
    $raw = file_get_contents(DATA_FILE);
    if ($raw === false) {
        throw new RuntimeException('Tidak bisa membaca data situs.');
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Format data situs tidak valid.');
    }

    return $data;
}

function save_site_data(array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Gagal mengubah data situs ke JSON.');
    }

    file_put_contents(DATA_FILE, $json);
}

function load_users(): array
{
    $raw = file_get_contents(USERS_FILE);
    if ($raw === false) {
        throw new RuntimeException('Tidak bisa membaca data user.');
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function save_users(array $users): void
{
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Gagal menyimpan data user.');
    }

    file_put_contents(USERS_FILE, $json);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function site_setting(array $site, string $key, string $fallback = ''): string
{
    return isset($site['settings'][$key]) ? (string) $site['settings'][$key] : $fallback;
}

function wa_link(array $site, string $message): string
{
    $phone = site_setting($site, 'phone_plain', '6281382197099');
    return 'https://wa.me/' . rawurlencode($phone) . '?text=' . rawurlencode($message);
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_username']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function authenticate_admin(string $username, string $password): bool
{
    $users = load_users();
    if (!isset($users[$username]['password_hash'])) {
        return false;
    }

    if (!password_verify($password, $users[$username]['password_hash'])) {
        return false;
    }

    $_SESSION['admin_username'] = $username;
    return true;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function flash_set(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $message;
}

function handle_upload(array $file, string $targetDir, array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg']): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload file gagal.');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Format file tidak didukung.');
    }

    $safeName = uniqid('asset_', true) . '.' . $extension;
    $targetPath = $targetDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Gagal memindahkan file upload.');
    }

    if (str_contains($targetDir, 'branding')) {
        $relativeBase = 'uploads/branding/';
    } elseif (str_contains($targetDir, 'videos')) {
        $relativeBase = 'uploads/videos/';
    } else {
        $relativeBase = 'uploads/gallery/';
    }

    return $relativeBase . $safeName;
}

function delete_file_if_exists(string $relativePath): void
{
    $fullPath = realpath(__DIR__ . '/..') . '/' . ltrim($relativePath, '/');
    if (is_file($fullPath) && str_contains($fullPath, realpath(__DIR__ . '/..') ?: '')) {
        unlink($fullPath);
    }
}

function next_content_id(string $prefix): string
{
    return $prefix . '_' . bin2hex(random_bytes(4));
}

function update_admin_password(string $username, string $currentPassword, string $newPassword): void
{
    $users = load_users();

    if (!isset($users[$username])) {
        throw new RuntimeException('User admin tidak ditemukan.');
    }

    if (!password_verify($currentPassword, $users[$username]['password_hash'])) {
        throw new RuntimeException('Password lama tidak cocok.');
    }

    if (strlen($newPassword) < 8) {
        throw new RuntimeException('Password baru minimal 8 karakter.');
    }

    $users[$username]['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
    save_users($users);
}

ensure_directories();
