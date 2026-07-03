<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!authenticate_admin($username, $password)) {
        $error = 'Username atau password salah.';
    } else {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Keepkeepclean</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <div class="page-shell admin-shell">
    <section class="admin-login-card">
      <p class="eyebrow">Admin panel</p>
      <h1 class="admin-title">Masuk untuk update harga, gambar, dan logo.</h1>
      <?php if ($error): ?>
        <div class="admin-alert admin-alert-error"><?= e($error) ?></div>
      <?php endif; ?>
      <form method="post" class="admin-form">
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" type="text" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required>
        </div>
        <button class="button button-primary" type="submit">Masuk</button>
      </form>
      <p class="field-note">Default login: <strong>admin</strong> / <strong>keepclean123</strong>. Ganti setelah deploy.</p>
    </section>
  </div>
</body>
</html>
