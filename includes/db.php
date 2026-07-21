<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db_driver(): string
{
    $explicit = strtolower((string) app_env('DB_DRIVER', ''));
    if (in_array($explicit, ['mysql', 'pgsql', 'sqlite'], true)) {
        return $explicit;
    }

    $port = (string) app_env('PORT', '');
    if ($port === '5432') {
        return 'pgsql';
    }

    return 'mysql';
}

function db_connection_candidates(): array
{
    $explicit = strtolower((string) app_env('DB_DRIVER', ''));
    $host = app_env('HOST', '127.0.0.1');
    $database = app_env('DATABASE', 'keepkeepclean');
    $username = app_env('USERNAME', 'root');
    $password = app_env('PASSWORD', '');
    $port = app_env('PORT', '');

    if ($explicit === 'sqlite') {
        return [[
            'driver' => 'sqlite',
            'dsn' => 'sqlite:' . base_path('data/database.sqlite'),
            'username' => null,
            'password' => null,
        ]];
    }

    $candidates = [];

    $addCandidate = static function (string $driver, string $candidatePort) use (&$candidates, $host, $database, $username, $password): void {
        $dsn = $driver === 'pgsql'
            ? sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $candidatePort, $database)
            : sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $candidatePort, $database);

        $key = $driver . ':' . $candidatePort;
        $candidates[$key] = [
            'driver' => $driver,
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password,
        ];
    };

    if ($explicit === 'pgsql' || $explicit === 'mysql') {
        $addCandidate($explicit, $port !== '' ? $port : ($explicit === 'pgsql' ? '5432' : '3306'));
        return array_values($candidates);
    }

    if ($port === '5432') {
        $addCandidate('pgsql', '5432');
        $addCandidate('mysql', '3306');
    } else {
        $addCandidate('mysql', $port !== '' ? $port : '3306');
        $addCandidate('pgsql', '5432');
    }

    return array_values($candidates);
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $lastException = null;

    foreach (db_connection_candidates() as $candidate) {
        try {
            $pdo = new PDO($candidate['dsn'], $candidate['username'], $candidate['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            return $pdo;
        } catch (PDOException $exception) {
            $lastException = $exception;
        }
    }

    throw new RuntimeException(
        'Koneksi database gagal. Periksa .env Anda (HOST/PORT/DATABASE/USERNAME/PASSWORD/DB_DRIVER).',
        previous: $lastException
    );
}

function db_bootstrap(): void
{
    static $bootstrapped = false;

    if ($bootstrapped) {
        return;
    }

    $pdo = db();
    $driver = db_driver();

    if ($driver === 'pgsql') {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS admin_users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ');
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS site_content (
                id INTEGER PRIMARY KEY,
                payload JSONB NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ');
    } elseif ($driver === 'sqlite') {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS admin_users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ');
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS site_content (
                id INTEGER PRIMARY KEY,
                payload TEXT NOT NULL,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ');
    } else {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS site_content (
                id INT PRIMARY KEY,
                payload LONGTEXT NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    seed_site_content($pdo);
    seed_admin_user($pdo);

    $bootstrapped = true;
}

function seed_site_content(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM site_content')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $json = file_get_contents(base_path('data/site.json'));
    if ($json === false) {
        throw new RuntimeException('Gagal membaca data/site.json.');
    }

    $stmt = $pdo->prepare('INSERT INTO site_content (id, payload) VALUES (1, :payload)');
    $stmt->execute(['payload' => $json]);
}

function seed_admin_user(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)');
    $stmt->execute([
        'username' => 'admin',
        'password_hash' => password_hash('KeepClean2026!', PASSWORD_DEFAULT),
    ]);
}

function site_payload(): array
{
    db_bootstrap();
    $row = db()->query('SELECT payload FROM site_content WHERE id = 1')->fetch();
    if (!$row) {
        throw new RuntimeException('Data website belum tersedia.');
    }

    $payload = json_decode((string) $row['payload'], true);
    if (!is_array($payload)) {
        throw new RuntimeException('Payload website tidak valid.');
    }

    return $payload;
}

function save_site_payload(array $payload): void
{
    db_bootstrap();
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Gagal mengubah payload website ke JSON.');
    }

    $stmt = db()->prepare('UPDATE site_content SET payload = :payload, updated_at = CURRENT_TIMESTAMP WHERE id = 1');
    $stmt->execute(['payload' => $json]);
}
