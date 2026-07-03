<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
$site = load_site_data();

render_head('Galeri Hasil Kerja | ' . site_setting($site, 'site_name'), $site);
render_header($site, 'galeri');
?>
<main>
  <section class="page-hero split-layout">
    <div class="gallery-copy" data-reveal>
      <p class="eyebrow">Galeri real job</p>
      <h1 class="page-title">Hasil kerja yang bisa langsung dipakai untuk bangun trust.</h1>
      <p class="page-subtitle">Gambar di halaman ini bisa diganti lewat admin panel tanpa edit code manual.</p>
    </div>
    <div class="hero-card" data-reveal>
      <img src="assets/process-poster.jpeg" alt="Poster proses kerja Keepkeepclean.">
    </div>
  </section>

  <section class="section">
    <div class="gallery-grid">
      <?php foreach ($site['gallery'] as $index => $item): ?>
        <figure class="gallery-card <?= $index === 0 ? 'gallery-card-large' : '' ?>" data-reveal>
          <img src="<?= e($item['path']) ?>" alt="<?= e($item['title']) ?>">
          <figcaption><?= e($item['title']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section">
    <div class="section-heading" data-reveal>
      <p class="eyebrow">Video pekerjaan</p>
      <h2>Konten video membantu customer yakin lebih cepat.</h2>
    </div>
    <div class="video-grid">
      <?php foreach ($site['videos'] as $video): ?>
        <article class="video-card" data-reveal>
          <video controls playsinline poster="<?= e($video['poster']) ?>">
            <source src="<?= e($video['path']) ?>" type="video/mp4">
          </video>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php render_footer($site); ?>
