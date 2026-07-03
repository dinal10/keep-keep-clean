<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function render_head(string $title, array $site): void
{
    $meta = site_setting($site, 'meta_description');
    echo '<!DOCTYPE html>';
    echo '<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . e($title) . '</title>';
    echo '<meta name="description" content="' . e($meta) . '">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Anton&family=Manrope:wght@400;500;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="styles.css">';
    echo '</head><body><div class="page-shell">';
}

function render_logo(array $site): void
{
    $brand = $site['branding'];
    $logoPath = e((string) ($brand['logo_path'] ?? 'assets/logo-power.svg'));
    $logoText = e((string) ($brand['logo_text'] ?? 'KEEPKEEPCLEAN'));

    echo '<span class="brand-lockup">';
    echo '<img class="brand-logo" src="' . $logoPath . '" alt="' . $logoText . '">';
    echo '</span>';
}

function render_header(array $site, string $active): void
{
    $menu = [
        'home' => ['label' => 'Home', 'href' => 'index.php'],
        'layanan' => ['label' => 'Layanan & Harga', 'href' => 'layanan.php'],
        'galeri' => ['label' => 'Galeri', 'href' => 'galeri.php'],
        'kontak' => ['label' => 'Kontak', 'href' => 'kontak.php'],
    ];

    echo '<header class="site-header">';
    echo '<a class="brand" href="index.php" aria-label="' . e(site_setting($site, 'site_name')) . '">';
    echo '<span class="brand-kicker">' . e(site_setting($site, 'tagline')) . '</span>';
    render_logo($site);
    echo '</a>';
    echo '<nav class="site-nav">';
    foreach ($menu as $key => $item) {
        $class = $key === $active ? ' class="is-active"' : '';
        echo '<a' . $class . ' href="' . e($item['href']) . '">' . e($item['label']) . '</a>';
    }
    echo '</nav>';
    echo '<a class="button button-primary" href="' . e(wa_link($site, 'Halo Keepkeepclean, saya ingin booking layanan.')) . '">Booking WA</a>';
    echo '</header>';
}

function render_footer(array $site): void
{
    echo '<footer class="site-footer">';
    echo '<div><strong>' . e(site_setting($site, 'site_name')) . '</strong><p>' . e(site_setting($site, 'footer_text')) . '</p></div>';
    echo '<a class="button button-secondary" href="admin/index.php">Admin Panel</a>';
    echo '</footer></div>';
    echo '<a class="floating-wa" href="' . e(wa_link($site, 'Halo Keepkeepclean, saya mau booking.')) . '" aria-label="Chat WhatsApp">WA</a>';
    echo '<script src="script.js"></script></body></html>';
}
