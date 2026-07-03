<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
$site = load_site_data();

render_head(site_setting($site, 'site_name') . ' | Hydro Vacuum & Wet Vacuum Premium', $site);
render_header($site, 'home');

$featuredGallery = array_slice($site['gallery'], 0, 3);
?>
<main>
  <section class="hero hero-home">
    <div class="hero-copy" data-reveal>
      <p class="eyebrow">Hydro vacuum / Wet vacuum / Shoe care</p>
      <h1><?= e(site_setting($site, 'hero_title')) ?></h1>
      <p class="hero-text"><?= e(site_setting($site, 'hero_description')) ?></p>
      <div class="hero-actions">
        <a class="button button-primary" href="<?= e(site_setting($site, 'hero_primary_link', 'layanan.php#estimasi')) ?>"><?= e(site_setting($site, 'hero_primary_label', 'Cek Estimasi Harga')) ?></a>
        <a class="button button-secondary" href="<?= e(wa_link($site, 'Halo Keepkeepclean, saya ingin konsultasi layanan.')) ?>"><?= e(site_setting($site, 'hero_secondary_label', 'Chat Admin')) ?></a>
      </div>
      <div class="stat-grid">
        <article class="stat-card"><strong><?= e(site_setting($site, 'hours_label')) ?></strong><span>Jam operasional</span></article>
        <article class="stat-card"><strong><?= e(site_setting($site, 'area_label')) ?></strong><span>Area layanan</span></article>
        <article class="stat-card"><strong><?= e(site_setting($site, 'phone_display')) ?></strong><span>Direct WhatsApp</span></article>
      </div>
    </div>

    <div class="hero-media" data-reveal>
      <div class="hero-card hero-card-video">
        <video autoplay muted loop playsinline poster="assets/process-poster.jpeg">
          <source src="assets/work-01.mp4" type="video/mp4">
        </video>
      </div>
      <div class="hero-card hero-card-image">
        <img src="assets/clean-poster.jpeg" alt="Teknisi Keepkeepclean sedang membersihkan sofa restoran.">
      </div>
      <div class="hero-badge">
        <span>Fast response</span>
        <strong><?= e(site_setting($site, 'instagram')) ?></strong>
      </div>
    </div>
  </section>

  <section class="proof-strip" data-reveal>
    <p>Visual real job, bukan stok foto.</p>
    <p>Setiap section diarahkan ke WhatsApp untuk conversion.</p>
    <p>Harga dan galeri bisa diubah sendiri dari admin panel.</p>
  </section>

  <section class="section">
    <div class="section-heading" data-reveal>
      <p class="eyebrow">Kenapa ini lebih kuat</p>
      <h2>Lebih sales-driven daripada halaman referensi.</h2>
      <p>Strukturnya dibuat untuk pelanggan yang datang dari bio Instagram, story, ads, atau broadcast WhatsApp.</p>
    </div>
    <div class="feature-grid">
      <article class="feature-card" data-reveal><span class="feature-tag">01</span><h3>Paket harga jelas</h3><p>Customer tidak harus menebak-nebak kisaran biaya sebelum chat.</p></article>
      <article class="feature-card" data-reveal><span class="feature-tag">02</span><h3>Estimator ke WhatsApp</h3><p>Form langsung menyusun detail order agar admin lebih cepat closing.</p></article>
      <article class="feature-card" data-reveal><span class="feature-tag">03</span><h3>Admin panel ringan</h3><p>Harga, teks, gambar galeri, dan logo bisa diperbarui dari browser.</p></article>
    </div>
  </section>

  <section class="section">
    <div class="section-heading" data-reveal>
      <p class="eyebrow">Layanan unggulan</p>
      <h2>Semua diarahkan ke layanan yang paling sering dicari.</h2>
    </div>
    <div class="service-grid">
      <?php foreach (array_slice($site['pricing'], 0, 4) as $item): ?>
        <article class="service-card" data-reveal>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['description']) ?></p>
          <a class="text-link" href="layanan.php#paket">Lihat paket</a>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section media-highlight">
    <div class="section-heading" data-reveal>
      <p class="eyebrow">Visual hasil</p>
      <h2>Bukti kerja nyata lebih cepat membangun trust.</h2>
    </div>
    <div class="gallery-grid">
      <?php foreach ($featuredGallery as $index => $item): ?>
        <figure class="gallery-card <?= $index === 0 ? 'gallery-card-large' : '' ?>" data-reveal>
          <img src="<?= e($item['path']) ?>" alt="<?= e($item['title']) ?>">
          <figcaption><?= e($item['title']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
    <div class="hero-actions" data-reveal>
      <a class="button button-secondary" href="galeri.php">Buka Galeri Lengkap</a>
    </div>
  </section>

  <section class="section cta-panel" data-reveal>
    <div>
      <p class="eyebrow">Next step</p>
      <h2>Kalau user sudah tertarik, jangan paksa isi form panjang.</h2>
      <p>Biarkan mereka pilih paket atau isi estimator, lalu arahkan langsung ke WhatsApp.</p>
    </div>
    <div class="cta-stack">
      <a class="button button-primary button-large" href="layanan.php#estimasi">Buka Estimator</a>
      <a class="button button-secondary" href="<?= e(wa_link($site, 'Halo Keepkeepclean, saya ingin booking hari ini.')) ?>">Book via WhatsApp</a>
    </div>
  </section>
</main>
<?php render_footer($site); ?>
