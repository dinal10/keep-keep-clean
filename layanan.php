<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
$site = load_site_data();

render_head('Layanan & Harga | ' . site_setting($site, 'site_name'), $site);
render_header($site, 'layanan');
?>
<main>
  <section class="page-hero split-layout">
    <div class="gallery-copy" data-reveal>
      <p class="eyebrow">Layanan dan paket</p>
      <h1 class="page-title">Harga dibuat jelas supaya customer lebih cepat masuk ke chat.</h1>
      <p class="page-subtitle">Angka di bawah bisa Anda ubah dari panel admin. Estimator otomatis akan menyusun detail order ke WhatsApp.</p>
    </div>
    <div class="hero-card" data-reveal>
      <img src="assets/brand-poster.jpeg" alt="Poster layanan Keepkeepclean.">
    </div>
  </section>

  <section class="section" id="paket">
    <div class="section-heading" data-reveal>
      <p class="eyebrow">Harga mulai dari</p>
      <h2>Paket yang langsung bisa dipasarkan.</h2>
    </div>
    <div class="pricing-grid">
      <?php foreach ($site['pricing'] as $item): ?>
        <article class="pricing-card <?= !empty($item['featured']) ? 'featured' : '' ?>" data-reveal>
          <span class="pricing-badge"><?= e($item['badge']) ?></span>
          <h3><?= e($item['title']) ?></h3>
          <span class="pricing-price"><?= e($item['price_label']) ?></span>
          <p><?= e($item['description']) ?></p>
          <ul class="pricing-list">
            <?php foreach ($item['features'] as $feature): ?>
              <li><?= e((string) $feature) ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section" id="estimasi">
    <div class="section-heading" data-reveal>
      <p class="eyebrow">Estimator otomatis</p>
      <h2>Isi beberapa field, lalu kirim estimasi langsung ke WhatsApp.</h2>
    </div>

    <div class="estimate-layout">
      <form class="estimate-card" data-estimator-form data-phone="<?= e(site_setting($site, 'phone_plain')) ?>" data-reveal>
        <div class="form-grid">
          <div class="field">
            <label for="name">Nama</label>
            <input id="name" name="name" type="text" placeholder="Nama Anda">
          </div>
          <div class="field">
            <label for="schedule">Jadwal yang diinginkan</label>
            <input id="schedule" name="schedule" type="text" placeholder="Contoh: Sabtu pagi">
          </div>
          <div class="field">
            <label for="service">Layanan</label>
            <select id="service" name="service">
              <?php foreach ($site['pricing'] as $item): ?>
                <?php if (in_array($item['id'], ['hydro', 'wet', 'disinfectant', 'shoe'], true)): ?>
                  <option value="<?= e($item['id']) ?>" data-price="<?= e((string) $item['price_value']) ?>" data-title="<?= e($item['title']) ?>"><?= e($item['title']) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="itemCount">Jumlah item / pasang</label>
            <input id="itemCount" name="itemCount" type="number" min="1" value="1">
          </div>
          <div class="field">
            <label for="area">Area layanan</label>
            <select id="area" name="area">
              <option value="local">Kembangan Utara / searah</option>
              <option value="west">Jakarta Barat lain</option>
              <option value="jakarta">DKI Jakarta lain</option>
              <option value="custom">Di luar area utama</option>
            </select>
          </div>
          <div class="field">
            <label for="shoeType">Tipe shoe care</label>
            <select id="shoeType" name="shoeType">
              <option value="standard">Basic cleaning</option>
              <option value="premium">Premium cleaning</option>
              <option value="repair">Cleaning + repair</option>
            </select>
          </div>
          <div class="field field-full">
            <label>Tambahan layanan</label>
            <div class="checkbox-grid">
              <label><input type="checkbox" name="extras" value="deodorizer"> Deodorizer</label>
              <label><input type="checkbox" name="extras" value="antiBacterial"> Anti-bacterial finish</label>
              <label><input type="checkbox" name="extras" value="express"> Priority slot</label>
            </div>
          </div>
          <div class="field field-full">
            <label for="details">Catatan tambahan</label>
            <textarea id="details" name="details" rows="5" placeholder="Contoh: sofa kain 3 seat, ada noda kopi, lokasi ruko."></textarea>
            <span class="field-note">Setelah klik kirim, pesan akan otomatis dibuka ke WhatsApp.</span>
          </div>
        </div>
        <div class="button-row">
          <button class="button button-primary" type="submit">Kirim Estimasi ke WA</button>
        </div>
      </form>

      <div class="estimate-sidebar">
        <article class="estimate-total" data-reveal>
          <span>Estimasi website</span>
          <strong data-estimate-total>Rp175.000</strong>
          <p data-estimate-breakdown>Layanan: Rp175.000 / Tambahan: Rp0 / Area: Rp0</p>
        </article>
        <article class="estimate-note" data-reveal>
          <span>Catatan</span>
          <strong>Ini kisaran awal, bukan invoice final.</strong>
          <p>Harga final bisa berubah tergantung ukuran objek, tingkat kotor, bahan, lokasi, dan kebutuhan treatment tambahan.</p>
        </article>
      </div>
    </div>
  </section>
</main>
<?php render_footer($site); ?>
