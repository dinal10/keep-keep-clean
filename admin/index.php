<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$site = load_site_data();
$success = flash_get('success');
$error = flash_get('error');
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$adminUsername = (string) ($_SESSION['admin_username'] ?? 'admin');

if ($requestMethod === 'POST') {
    try {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'update_settings') {
            $fields = [
                'site_name', 'tagline', 'phone_display', 'phone_plain', 'hero_title', 'hero_description',
                'hero_primary_label', 'hero_secondary_label', 'hero_primary_link', 'area_label',
                'hours_label', 'contact_email', 'instagram', 'footer_text', 'meta_description'
            ];

            foreach ($fields as $field) {
                $site['settings'][$field] = trim((string) ($_POST[$field] ?? ''));
            }
            save_site_data($site);
            flash_set('success', 'Pengaturan website berhasil disimpan.');
        }

        if ($action === 'update_pricing') {
            foreach ($site['pricing'] as $index => $item) {
                $site['pricing'][$index]['badge'] = trim((string) ($_POST['badge'][$index] ?? $item['badge']));
                $site['pricing'][$index]['title'] = trim((string) ($_POST['title'][$index] ?? $item['title']));
                $site['pricing'][$index]['price_label'] = trim((string) ($_POST['price_label'][$index] ?? $item['price_label']));
                $site['pricing'][$index]['price_value'] = (int) ($_POST['price_value'][$index] ?? $item['price_value']);
                $site['pricing'][$index]['description'] = trim((string) ($_POST['description'][$index] ?? $item['description']));
                $featuresRaw = trim((string) ($_POST['features'][$index] ?? ''));
                $site['pricing'][$index]['features'] = array_values(array_filter(array_map('trim', explode("\n", $featuresRaw))));
            }
            save_site_data($site);
            flash_set('success', 'Harga dan paket berhasil diperbarui.');
        }

        if ($action === 'update_gallery') {
            foreach ($site['gallery'] as $index => $item) {
                $title = trim((string) ($_POST['gallery_title'][$index] ?? $item['title']));
                $uploadKey = 'gallery_image_' . $index;
                $newPath = handle_upload($_FILES[$uploadKey] ?? [], UPLOAD_GALLERY_DIR);
                $site['gallery'][$index]['title'] = $title;
                if ($newPath) {
                    $site['gallery'][$index]['path'] = $newPath;
                }
            }
            save_site_data($site);
            flash_set('success', 'Galeri berhasil diperbarui.');
        }

        if ($action === 'add_gallery') {
            $title = trim((string) ($_POST['new_gallery_title'] ?? ''));
            $path = handle_upload($_FILES['new_gallery_file'] ?? [], UPLOAD_GALLERY_DIR);
            if ($title === '' || !$path) {
                throw new RuntimeException('Judul dan file gambar baru wajib diisi.');
            }

            $site['gallery'][] = [
                'id' => next_content_id('g'),
                'title' => $title,
                'path' => $path,
                'type' => 'image',
                'featured' => false,
            ];
            save_site_data($site);
            flash_set('success', 'Gambar galeri baru berhasil ditambahkan.');
        }

        if ($action === 'delete_gallery') {
            $deleteId = (string) ($_POST['delete_id'] ?? '');
            $updated = [];
            foreach ($site['gallery'] as $item) {
                if (($item['id'] ?? '') === $deleteId) {
                    if (str_starts_with((string) $item['path'], 'uploads/gallery/')) {
                        delete_file_if_exists((string) $item['path']);
                    }
                    continue;
                }
                $updated[] = $item;
            }
            $site['gallery'] = array_values($updated);
            save_site_data($site);
            flash_set('success', 'Item galeri berhasil dihapus.');
        }

        if ($action === 'update_videos') {
            foreach ($site['videos'] as $index => $item) {
                $site['videos'][$index]['title'] = trim((string) ($_POST['video_title'][$index] ?? $item['title']));
                $videoPath = handle_upload($_FILES['video_file_' . $index] ?? [], UPLOAD_VIDEO_DIR, ['mp4', 'webm', 'mov']);
                $posterPath = handle_upload($_FILES['video_poster_' . $index] ?? [], UPLOAD_GALLERY_DIR, ['jpg', 'jpeg', 'png', 'webp']);
                if ($videoPath) {
                    $site['videos'][$index]['path'] = $videoPath;
                }
                if ($posterPath) {
                    $site['videos'][$index]['poster'] = $posterPath;
                }
            }
            save_site_data($site);
            flash_set('success', 'Video berhasil diperbarui.');
        }

        if ($action === 'add_video') {
            $title = trim((string) ($_POST['new_video_title'] ?? ''));
            $videoPath = handle_upload($_FILES['new_video_file'] ?? [], UPLOAD_VIDEO_DIR, ['mp4', 'webm', 'mov']);
            $posterPath = handle_upload($_FILES['new_video_poster'] ?? [], UPLOAD_GALLERY_DIR, ['jpg', 'jpeg', 'png', 'webp']);
            if ($title === '' || !$videoPath || !$posterPath) {
                throw new RuntimeException('Judul, file video, dan poster wajib diisi.');
            }

            $site['videos'][] = [
                'id' => next_content_id('v'),
                'title' => $title,
                'path' => $videoPath,
                'poster' => $posterPath,
            ];
            save_site_data($site);
            flash_set('success', 'Video baru berhasil ditambahkan.');
        }

        if ($action === 'delete_video') {
            $deleteId = (string) ($_POST['delete_id'] ?? '');
            $updated = [];
            foreach ($site['videos'] as $item) {
                if (($item['id'] ?? '') === $deleteId) {
                    if (str_starts_with((string) $item['path'], 'uploads/videos/')) {
                        delete_file_if_exists((string) $item['path']);
                    }
                    if (str_starts_with((string) $item['poster'], 'uploads/gallery/')) {
                        delete_file_if_exists((string) $item['poster']);
                    }
                    continue;
                }
                $updated[] = $item;
            }
            $site['videos'] = array_values($updated);
            save_site_data($site);
            flash_set('success', 'Video berhasil dihapus.');
        }

        if ($action === 'update_branding') {
            $site['branding']['logo_text'] = trim((string) ($_POST['logo_text'] ?? $site['branding']['logo_text']));
            $logoPath = handle_upload($_FILES['logo_file'] ?? [], UPLOAD_BRANDING_DIR, ['jpg', 'jpeg', 'png', 'webp', 'svg']);
            if ($logoPath) {
                $site['branding']['logo_path'] = $logoPath;
                $site['branding']['logo_type'] = 'upload';
            }
            save_site_data($site);
            flash_set('success', 'Branding berhasil diperbarui.');
        }

        if ($action === 'change_password') {
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if ($newPassword !== $confirmPassword) {
                throw new RuntimeException('Konfirmasi password baru tidak sama.');
            }

            update_admin_password($adminUsername, $currentPassword, $newPassword);
            flash_set('success', 'Password admin berhasil diubah.');
        }
    } catch (Throwable $exception) {
        flash_set('error', $exception->getMessage());
    }

    header('Location: index.php');
    exit;
}

$site = load_site_data();
$stats = [
    'total_paket' => count($site['pricing']),
    'total_galeri' => count($site['gallery']),
    'total_video' => count($site['videos']),
    'nomor_wa' => site_setting($site, 'phone_display'),
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel | Keepkeepclean</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <div class="page-shell admin-shell">
    <header class="admin-topbar">
      <div>
        <p class="eyebrow">Admin control panel</p>
        <h1 class="admin-title">Update harga, gambar, video, konten, logo, dan password dari browser.</h1>
      </div>
      <div class="button-row">
        <a class="button button-secondary" href="../index.php" target="_blank" rel="noreferrer">Lihat Website</a>
        <a class="button button-secondary" href="logout.php">Logout</a>
      </div>
    </header>

    <?php if ($success): ?>
      <div class="admin-alert admin-alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="admin-alert admin-alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="admin-dashboard">
      <article class="admin-stat-card">
        <span class="contact-label">Total paket</span>
        <strong><?= e((string) $stats['total_paket']) ?></strong>
      </article>
      <article class="admin-stat-card">
        <span class="contact-label">Total galeri</span>
        <strong><?= e((string) $stats['total_galeri']) ?></strong>
      </article>
      <article class="admin-stat-card">
        <span class="contact-label">Total video</span>
        <strong><?= e((string) $stats['total_video']) ?></strong>
      </article>
      <article class="admin-stat-card">
        <span class="contact-label">Nomor WA aktif</span>
        <strong><?= e($stats['nomor_wa']) ?></strong>
      </article>
    </section>

    <section class="admin-section">
      <div class="section-heading">
        <p class="eyebrow">Quick links</p>
        <h2>Akses cepat</h2>
      </div>
      <div class="button-row">
        <a class="button button-secondary" href="../layanan.php#estimasi" target="_blank" rel="noreferrer">Buka Estimator</a>
        <a class="button button-secondary" href="../galeri.php" target="_blank" rel="noreferrer">Buka Galeri</a>
        <a class="button button-secondary" href="<?= e(wa_link($site, 'Halo Keepkeepclean, saya ingin booking layanan.')) ?>" target="_blank" rel="noreferrer">Test WA</a>
      </div>
    </section>

    <section class="admin-section">
      <div class="section-heading">
        <p class="eyebrow">Website settings</p>
        <h2>Konten utama</h2>
      </div>
      <form method="post" class="admin-form-grid">
        <input type="hidden" name="action" value="update_settings">
        <?php
        $settingsFields = [
            'site_name' => 'Nama situs',
            'tagline' => 'Tagline',
            'phone_display' => 'Nomor tampil',
            'phone_plain' => 'Nomor plain WA',
            'hero_title' => 'Hero title',
            'hero_description' => 'Hero description',
            'hero_primary_label' => 'Label CTA utama',
            'hero_secondary_label' => 'Label CTA kedua',
            'hero_primary_link' => 'Link CTA utama',
            'area_label' => 'Area layanan',
            'hours_label' => 'Jam operasional',
            'contact_email' => 'Email',
            'instagram' => 'Instagram',
            'footer_text' => 'Footer text',
            'meta_description' => 'Meta description'
        ];
        foreach ($settingsFields as $field => $label): ?>
          <div class="field <?= in_array($field, ['hero_title', 'hero_description', 'footer_text', 'meta_description'], true) ? 'field-full' : '' ?>">
            <label for="<?= e($field) ?>"><?= e($label) ?></label>
            <?php if (in_array($field, ['hero_description', 'footer_text', 'meta_description'], true)): ?>
              <textarea id="<?= e($field) ?>" name="<?= e($field) ?>" rows="4"><?= e((string) $site['settings'][$field]) ?></textarea>
            <?php else: ?>
              <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="text" value="<?= e((string) $site['settings'][$field]) ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <div class="button-row field-full">
          <button class="button button-primary" type="submit">Simpan Pengaturan</button>
        </div>
      </form>
    </section>

    <section class="admin-section">
      <div class="section-heading">
        <p class="eyebrow">Pricing</p>
        <h2>Harga dan paket</h2>
      </div>
      <form method="post" class="admin-stack">
        <input type="hidden" name="action" value="update_pricing">
        <?php foreach ($site['pricing'] as $index => $item): ?>
          <article class="admin-card">
            <div class="admin-form-grid">
              <div class="field">
                <label>Badge</label>
                <input name="badge[<?= $index ?>]" type="text" value="<?= e($item['badge']) ?>">
              </div>
              <div class="field">
                <label>Title</label>
                <input name="title[<?= $index ?>]" type="text" value="<?= e($item['title']) ?>">
              </div>
              <div class="field">
                <label>Price label</label>
                <input name="price_label[<?= $index ?>]" type="text" value="<?= e($item['price_label']) ?>">
              </div>
              <div class="field">
                <label>Price value</label>
                <input name="price_value[<?= $index ?>]" type="number" value="<?= e((string) $item['price_value']) ?>">
              </div>
              <div class="field field-full">
                <label>Description</label>
                <textarea name="description[<?= $index ?>]" rows="3"><?= e($item['description']) ?></textarea>
              </div>
              <div class="field field-full">
                <label>Features (satu baris satu item)</label>
                <textarea name="features[<?= $index ?>]" rows="4"><?= e(implode("\n", $item['features'])) ?></textarea>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
        <div class="button-row">
          <button class="button button-primary" type="submit">Simpan Harga</button>
        </div>
      </form>
    </section>

    <section class="admin-section">
      <div class="section-heading">
        <p class="eyebrow">Gallery</p>
        <h2>CRUD galeri gambar</h2>
      </div>
      <form method="post" enctype="multipart/form-data" class="admin-stack">
        <input type="hidden" name="action" value="update_gallery">
        <?php foreach ($site['gallery'] as $index => $item): ?>
          <article class="admin-card admin-gallery-row">
            <img src="../<?= e($item['path']) ?>" alt="<?= e($item['title']) ?>" class="admin-thumb">
            <div class="admin-form-grid">
              <div class="field">
                <label>Judul gambar</label>
                <input name="gallery_title[<?= $index ?>]" type="text" value="<?= e($item['title']) ?>">
              </div>
              <div class="field">
                <label>Upload gambar baru</label>
                <input name="gallery_image_<?= $index ?>" type="file" accept=".jpg,.jpeg,.png,.webp,.svg">
              </div>
              <div class="button-row field-full">
                <button class="button button-primary" type="submit">Simpan Perubahan</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </form>
      <form method="post" enctype="multipart/form-data" class="admin-card admin-form-grid">
        <input type="hidden" name="action" value="add_gallery">
        <div class="field">
          <label for="new_gallery_title">Judul gambar baru</label>
          <input id="new_gallery_title" name="new_gallery_title" type="text">
        </div>
        <div class="field">
          <label for="new_gallery_file">File gambar baru</label>
          <input id="new_gallery_file" name="new_gallery_file" type="file" accept=".jpg,.jpeg,.png,.webp,.svg">
        </div>
        <div class="button-row field-full">
          <button class="button button-primary" type="submit">Tambah Gambar</button>
        </div>
      </form>
      <div class="admin-stack">
        <?php foreach ($site['gallery'] as $item): ?>
          <form method="post" class="admin-inline-form">
            <input type="hidden" name="action" value="delete_gallery">
            <input type="hidden" name="delete_id" value="<?= e($item['id']) ?>">
            <span>Hapus: <?= e($item['title']) ?></span>
            <button class="button button-danger" type="submit">Hapus</button>
          </form>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="admin-section">
      <div class="section-heading">
        <p class="eyebrow">Videos</p>
        <h2>CRUD video</h2>
      </div>
      <form method="post" enctype="multipart/form-data" class="admin-stack">
        <input type="hidden" name="action" value="update_videos">
        <?php foreach ($site['videos'] as $index => $item): ?>
          <article class="admin-card admin-gallery-row">
            <video class="admin-thumb" controls poster="../<?= e($item['poster']) ?>">
              <source src="../<?= e($item['path']) ?>" type="video/mp4">
            </video>
            <div class="admin-form-grid">
              <div class="field">
                <label>Judul video</label>
                <input name="video_title[<?= $index ?>]" type="text" value="<?= e($item['title']) ?>">
              </div>
              <div class="field">
                <label>Upload video baru</label>
                <input name="video_file_<?= $index ?>" type="file" accept=".mp4,.webm,.mov">
              </div>
              <div class="field">
                <label>Upload poster baru</label>
                <input name="video_poster_<?= $index ?>" type="file" accept=".jpg,.jpeg,.png,.webp">
              </div>
              <div class="button-row field-full">
                <button class="button button-primary" type="submit">Simpan Perubahan</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </form>
      <form method="post" enctype="multipart/form-data" class="admin-card admin-form-grid">
        <input type="hidden" name="action" value="add_video">
        <div class="field">
          <label for="new_video_title">Judul video baru</label>
          <input id="new_video_title" name="new_video_title" type="text">
        </div>
        <div class="field">
          <label for="new_video_file">File video baru</label>
          <input id="new_video_file" name="new_video_file" type="file" accept=".mp4,.webm,.mov">
        </div>
        <div class="field field-full">
          <label for="new_video_poster">Poster video baru</label>
          <input id="new_video_poster" name="new_video_poster" type="file" accept=".jpg,.jpeg,.png,.webp">
        </div>
        <div class="button-row field-full">
          <button class="button button-primary" type="submit">Tambah Video</button>
        </div>
      </form>
      <div class="admin-stack">
        <?php foreach ($site['videos'] as $item): ?>
          <form method="post" class="admin-inline-form">
            <input type="hidden" name="action" value="delete_video">
            <input type="hidden" name="delete_id" value="<?= e($item['id']) ?>">
            <span>Hapus: <?= e($item['title']) ?></span>
            <button class="button button-danger" type="submit">Hapus</button>
          </form>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="admin-section">
      <div class="section-heading">
        <p class="eyebrow">Branding</p>
        <h2>Logo dan identitas</h2>
      </div>
      <form method="post" enctype="multipart/form-data" class="admin-form-grid">
        <input type="hidden" name="action" value="update_branding">
        <div class="field">
          <label for="logo_text">Logo text</label>
          <input id="logo_text" name="logo_text" type="text" value="<?= e($site['branding']['logo_text']) ?>">
        </div>
        <div class="field">
          <label for="logo_file">Upload logo baru</label>
          <input id="logo_file" name="logo_file" type="file" accept=".jpg,.jpeg,.png,.webp,.svg">
        </div>
        <div class="field field-full">
          <label>Logo aktif</label>
          <div class="admin-logo-preview">
            <img src="../<?= e($site['branding']['logo_path']) ?>" alt="Logo aktif">
          </div>
        </div>
        <div class="button-row field-full">
          <button class="button button-primary" type="submit">Simpan Branding</button>
        </div>
      </form>
    </section>

    <section class="admin-section">
      <div class="section-heading">
        <p class="eyebrow">Security</p>
        <h2>Ganti password admin</h2>
      </div>
      <form method="post" class="admin-form-grid">
        <input type="hidden" name="action" value="change_password">
        <div class="field">
          <label for="current_password">Password lama</label>
          <input id="current_password" name="current_password" type="password" required>
        </div>
        <div class="field">
          <label for="new_password">Password baru</label>
          <input id="new_password" name="new_password" type="password" required>
        </div>
        <div class="field field-full">
          <label for="confirm_password">Konfirmasi password baru</label>
          <input id="confirm_password" name="confirm_password" type="password" required>
        </div>
        <div class="button-row field-full">
          <button class="button button-primary" type="submit">Ubah Password</button>
        </div>
      </form>
    </section>
  </div>
</body>
</html>
