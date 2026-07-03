<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
$site = load_site_data();

render_head('Kontak & Booking | ' . site_setting($site, 'site_name'), $site);
render_header($site, 'kontak');
?>
<main>
  <section class="page-hero split-layout">
    <div class="gallery-copy" data-reveal>
      <p class="eyebrow">Kontak dan booking</p>
      <h1 class="page-title">Satu halaman khusus untuk closing.</h1>
      <p class="page-subtitle">Bagikan halaman ini saat customer sudah siap order dan tinggal butuh jalur kontak yang cepat.</p>
    </div>
    <div class="hero-card" data-reveal>
      <img src="assets/brand-poster.jpeg" alt="Poster Keepkeepclean.">
    </div>
  </section>

  <section class="section contact-grid">
    <div class="contact-card" data-reveal>
      <span class="contact-label">WhatsApp utama</span>
      <h3><?= e(site_setting($site, 'phone_display')) ?></h3>
      <p>Semua tombol booking di website ini diarahkan ke nomor tersebut.</p>
      <div class="button-row">
        <a class="button button-primary" href="<?= e(wa_link($site, 'Halo Keepkeepclean, saya siap booking.')) ?>">Chat WhatsApp</a>
        <a class="button button-secondary" href="layanan.php#estimasi">Isi Estimator</a>
      </div>
    </div>

    <div class="contact-card" data-reveal>
      <span class="contact-label">Jam operasional</span>
      <h3><?= e(site_setting($site, 'hours_label')) ?></h3>
      <p>Untuk area luar jam utama, admin bisa arahkan ke slot terdekat yang tersedia.</p>
    </div>

    <div class="contact-card" data-reveal>
      <span class="contact-label">Area layanan</span>
      <h3><?= e(site_setting($site, 'area_label')) ?></h3>
      <p>Untuk area lain, tetap bisa konsultasi dulu melalui WhatsApp agar admin cek biaya transport dan jadwal.</p>
    </div>

    <div class="contact-card" data-reveal>
      <span class="contact-label">Yang perlu dikirim saat chat</span>
      <ul class="contact-list">
        <li>Foto objek yang ingin dibersihkan atau diperbaiki</li>
        <li>Jumlah item atau pasangan sepatu</li>
        <li>Lokasi dan jadwal yang diinginkan</li>
        <li>Kondisi khusus seperti noda, bau, atau kerusakan</li>
      </ul>
    </div>
  </section>
</main>
<?php render_footer($site); ?>
