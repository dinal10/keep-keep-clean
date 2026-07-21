const SITE_DATA_URL = "includes/api/site.php";

const state = {
  site: null,
};

const formatCurrency = value =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(value);

const escapeHtml = value =>
  String(value).replace(/[&<>"']/g, char => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  }[char]));

const waLink = (phone, message) =>
  `https://wa.me/${encodeURIComponent(phone)}?text=${encodeURIComponent(message)}`;

async function loadSite() {
  const response = await fetch(SITE_DATA_URL, { cache: "no-store" });
  if (!response.ok) throw new Error("Gagal memuat data situs.");
  state.site = await response.json();
  return state.site;
}

function renderHeader(active) {
  const header = document.querySelector("[data-site-header]");
  const { settings, branding } = state.site;
  const items = [
    ["home", "Home", "index.html"],
    ["layanan", "Layanan & Harga", "layanan.html"],
    ["galeri", "Galeri", "galeri.html"],
    ["kontak", "Kontak", "kontak.html"],
  ];

  header.innerHTML = `
    <a class="brand" href="index.html" aria-label="${escapeHtml(settings.site_name)}">
      <span class="brand-kicker">${escapeHtml(settings.tagline)}</span>
      <span class="brand-lockup"><img class="brand-logo" src="${escapeHtml(branding.logo_path)}" alt="${escapeHtml(branding.logo_text)}"></span>
    </a>
    <nav class="site-nav">
      ${items.map(([key, label, href]) => `<a class="${active === key ? "is-active" : ""}" href="${href}">${label}</a>`).join("")}
    </nav>
    <a class="button button-primary" href="${waLink(settings.phone_plain, "Halo Keep Clean, saya ingin booking layanan.")}">Booking WA</a>
  `;
}

function renderFooter() {
  const footer = document.querySelector("[data-site-footer]");
  const { settings } = state.site;
  footer.innerHTML = `
    <div>
      <strong>${escapeHtml(settings.site_name)}</strong>
      <p>${escapeHtml(settings.footer_text)}</p>
    </div>
    <a class="button button-secondary" href="admin/index.html">Admin Panel</a>
  `;
}

function hydrateMeta() {
  const { settings } = state.site;
  document.title = document.title.includes("|") ? document.title : `${settings.site_name} | Hydro Vacuum & Wet Vacuum Premium`;
  document.querySelector('meta[name="description"]').setAttribute("content", settings.meta_description);
  document.querySelectorAll("[data-wa-link]").forEach(node => {
    node.href = waLink(settings.phone_plain, node.dataset.waLink);
  });
}

function renderHome(root) {
  const { settings, pricing, gallery } = state.site;
  const featured = gallery.slice(0, 3);
  root.innerHTML = `
    <section class="hero hero-home">
      <div class="hero-copy" data-reveal>
        <p class="eyebrow">Hydro vacuum / Wet vacuum / Shoe care</p>
        <h1>${escapeHtml(settings.hero_title)}</h1>
        <p class="hero-text">${escapeHtml(settings.hero_description)}</p>
        <div class="hero-actions">
          <a class="button button-primary" href="${escapeHtml(settings.hero_primary_link)}">${escapeHtml(settings.hero_primary_label)}</a>
          <a class="button button-secondary" href="${waLink(settings.phone_plain, "Halo Keep Clean, saya ingin konsultasi layanan.")}">${escapeHtml(settings.hero_secondary_label)}</a>
        </div>
        <div class="stat-grid">
          <article class="stat-card"><strong>${escapeHtml(settings.hours_label)}</strong><span>Jam operasional</span></article>
          <article class="stat-card"><strong>${escapeHtml(settings.area_label)}</strong><span>Area layanan</span></article>
          <article class="stat-card"><strong>${escapeHtml(settings.phone_display)}</strong><span>Direct WhatsApp</span></article>
        </div>
      </div>
      <div class="hero-media" data-reveal>
        <div class="hero-card hero-card-video">
          <video autoplay muted loop playsinline poster="assets/process-poster.jpeg">
            <source src="assets/work-01.mp4" type="video/mp4">
          </video>
        </div>
        <div class="hero-card hero-card-image">
          <img src="assets/clean-poster.jpeg" alt="Teknisi Keep Clean sedang membersihkan sofa restoran.">
        </div>
        <div class="hero-badge">
          <span>Fast response</span>
          <strong>${escapeHtml(settings.instagram)}</strong>
        </div>
      </div>
    </section>
    <section class="proof-strip" data-reveal>
      <p>Visual real job, bukan stok foto.</p>
      <p>Setiap section diarahkan ke WhatsApp untuk conversion.</p>
      <p>Harga dan galeri bisa diubah sendiri dari admin panel berbasis database.</p>
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
        <article class="feature-card" data-reveal><span class="feature-tag">03</span><h3>Admin panel berbasis database</h3><p>Konten disimpan di MySQL dan bisa dikelola langsung dari browser.</p></article>
      </div>
    </section>
    <section class="section">
      <div class="section-heading" data-reveal>
        <p class="eyebrow">Layanan unggulan</p>
        <h2>Semua diarahkan ke layanan yang paling sering dicari.</h2>
      </div>
      <div class="service-grid">
        ${pricing.slice(0, 4).map(item => `
          <article class="service-card" data-reveal>
            <h3>${escapeHtml(item.title)}</h3>
            <p>${escapeHtml(item.description)}</p>
            <a class="text-link" href="layanan.html#paket">Lihat paket</a>
          </article>
        `).join("")}
      </div>
    </section>
    <section class="section media-highlight">
      <div class="section-heading" data-reveal>
        <p class="eyebrow">Visual hasil</p>
        <h2>Bukti kerja nyata lebih cepat membangun trust.</h2>
      </div>
      <div class="gallery-grid">
        ${featured.map((item, index) => `
          <figure class="gallery-card ${index === 0 ? "gallery-card-large" : ""}" data-reveal>
            <img src="${escapeHtml(item.path)}" alt="${escapeHtml(item.title)}">
            <figcaption>${escapeHtml(item.title)}</figcaption>
          </figure>
        `).join("")}
      </div>
      <div class="hero-actions" data-reveal><a class="button button-secondary" href="galeri.html">Buka Galeri Lengkap</a></div>
    </section>
    <section class="section cta-panel" data-reveal>
      <div>
        <p class="eyebrow">Next step</p>
        <h2>Kalau user sudah tertarik, jangan paksa isi form panjang.</h2>
        <p>Biarkan mereka pilih paket atau isi estimator, lalu arahkan langsung ke WhatsApp.</p>
      </div>
      <div class="cta-stack">
        <a class="button button-primary button-large" href="layanan.html#estimasi">Buka Estimator</a>
        <a class="button button-secondary" href="${waLink(settings.phone_plain, "Halo Keep Clean, saya ingin booking hari ini.")}">Book via WhatsApp</a>
      </div>
    </section>
  `;
}

function renderLayanan(root) {
  const { pricing, settings } = state.site;
  root.innerHTML = `
    <section class="page-hero split-layout">
      <div class="gallery-copy" data-reveal>
        <p class="eyebrow">Layanan dan paket</p>
        <h1 class="page-title">Harga dibuat jelas supaya customer lebih cepat masuk ke chat.</h1>
        <p class="page-subtitle">Angka di bawah bisa Anda ubah dari panel admin. Estimator otomatis akan menyusun detail order ke WhatsApp.</p>
      </div>
      <div class="hero-card" data-reveal><img src="assets/brand-poster.jpeg" alt="Poster layanan Keep Clean."></div>
    </section>
    <section class="section" id="paket">
      <div class="section-heading" data-reveal><p class="eyebrow">Harga mulai dari</p><h2>Paket yang langsung bisa dipasarkan.</h2></div>
      <div class="pricing-grid">
        ${pricing.map(item => `
          <article class="pricing-card ${item.featured ? "featured" : ""}" data-reveal>
            <span class="pricing-badge">${escapeHtml(item.badge)}</span>
            <h3>${escapeHtml(item.title)}</h3>
            <span class="pricing-price">${escapeHtml(item.price_label)}</span>
            <p>${escapeHtml(item.description)}</p>
            <ul class="pricing-list">${item.features.map(feature => `<li>${escapeHtml(feature)}</li>`).join("")}</ul>
          </article>
        `).join("")}
      </div>
    </section>
    <section class="section" id="estimasi">
      <div class="section-heading" data-reveal><p class="eyebrow">Estimator otomatis</p><h2>Isi beberapa field, lalu kirim estimasi langsung ke WhatsApp.</h2></div>
      <div class="estimate-layout">
        <form class="estimate-card" data-estimator-form data-phone="${escapeHtml(settings.phone_plain)}" data-reveal>
          <div class="form-grid">
            <div class="field"><label for="name">Nama</label><input id="name" name="name" type="text" placeholder="Nama Anda"></div>
            <div class="field"><label for="schedule">Jadwal yang diinginkan</label><input id="schedule" name="schedule" type="text" placeholder="Contoh: Sabtu pagi"></div>
            <div class="field"><label for="service">Layanan</label><select id="service" name="service">
              ${pricing.filter(item => ["hydro", "wet", "disinfectant", "shoe"].includes(item.id)).map(item => `<option value="${escapeHtml(item.id)}" data-price="${item.price_value}" data-title="${escapeHtml(item.title)}">${escapeHtml(item.title)}</option>`).join("")}
            </select></div>
            <div class="field"><label for="itemCount">Jumlah item / pasang</label><input id="itemCount" name="itemCount" type="number" min="1" value="1"></div>
            <div class="field"><label for="area">Area layanan</label><select id="area" name="area"><option value="local">Kembangan Utara / searah</option><option value="west">Jakarta Barat lain</option><option value="jakarta">DKI Jakarta lain</option><option value="custom">Di luar area utama</option></select></div>
            <div class="field"><label for="shoeType">Tipe shoe care</label><select id="shoeType" name="shoeType"><option value="standard">Basic cleaning</option><option value="premium">Premium cleaning</option><option value="repair">Cleaning + repair</option></select></div>
            <div class="field field-full"><label>Tambahan layanan</label><div class="checkbox-grid"><label><input type="checkbox" name="extras" value="deodorizer"> Deodorizer</label><label><input type="checkbox" name="extras" value="antiBacterial"> Anti-bacterial finish</label><label><input type="checkbox" name="extras" value="express"> Priority slot</label></div></div>
            <div class="field field-full"><label for="details">Catatan tambahan</label><textarea id="details" name="details" rows="5" placeholder="Contoh: sofa kain 3 seat, ada noda kopi, lokasi ruko."></textarea><span class="field-note">Setelah klik kirim, pesan akan otomatis dibuka ke WhatsApp.</span></div>
          </div>
          <div class="button-row"><button class="button button-primary" type="submit">Kirim Estimasi ke WA</button></div>
        </form>
        <div class="estimate-sidebar">
          <article class="estimate-total" data-reveal><span>Estimasi website</span><strong data-estimate-total>Rp175.000</strong><p data-estimate-breakdown>Layanan: Rp175.000 / Tambahan: Rp0 / Area: Rp0</p></article>
          <article class="estimate-note" data-reveal><span>Catatan</span><strong>Ini kisaran awal, bukan invoice final.</strong><p>Harga final bisa berubah tergantung ukuran objek, tingkat kotor, bahan, lokasi, dan kebutuhan treatment tambahan.</p></article>
        </div>
      </div>
    </section>
  `;
}

function renderGaleri(root) {
  const { gallery, videos } = state.site;
  root.innerHTML = `
    <section class="page-hero split-layout">
      <div class="gallery-copy" data-reveal>
        <p class="eyebrow">Galeri real job</p>
        <h1 class="page-title">Hasil kerja yang bisa langsung dipakai untuk bangun trust.</h1>
        <p class="page-subtitle">Gambar dan video halaman ini dibaca dari database dan bisa diperbarui dari panel admin.</p>
      </div>
      <div class="hero-card" data-reveal><img src="assets/process-poster.jpeg" alt="Poster proses kerja Keep Clean."></div>
    </section>
    <section class="section"><div class="gallery-grid">${gallery.map((item, index) => `
      <figure class="gallery-card ${index === 0 ? "gallery-card-large" : ""}" data-reveal><img src="${escapeHtml(item.path)}" alt="${escapeHtml(item.title)}"><figcaption>${escapeHtml(item.title)}</figcaption></figure>
    `).join("")}</div></section>
    <section class="section">
      <div class="section-heading" data-reveal><p class="eyebrow">Video pekerjaan</p><h2>Konten video membantu customer yakin lebih cepat.</h2></div>
      <div class="video-grid">${videos.map(video => `
        <article class="video-card" data-reveal><video controls playsinline poster="${escapeHtml(video.poster)}"><source src="${escapeHtml(video.path)}" type="video/mp4"></video></article>
      `).join("")}</div>
    </section>
  `;
}

function renderKontak(root) {
  const { settings } = state.site;
  root.innerHTML = `
    <section class="page-hero split-layout">
      <div class="gallery-copy" data-reveal>
        <p class="eyebrow">Kontak dan booking</p>
        <h1 class="page-title">Satu halaman khusus untuk closing.</h1>
        <p class="page-subtitle">Bagikan halaman ini saat customer sudah siap order dan tinggal butuh jalur kontak yang cepat.</p>
      </div>
      <div class="hero-card" data-reveal><img src="assets/brand-poster.jpeg" alt="Poster Keep Clean."></div>
    </section>
    <section class="section contact-grid">
      <div class="contact-card" data-reveal><span class="contact-label">WhatsApp utama</span><h3>${escapeHtml(settings.phone_display)}</h3><p>Semua tombol booking di website ini diarahkan ke nomor tersebut.</p><div class="button-row"><a class="button button-primary" href="${waLink(settings.phone_plain, "Halo Keep Clean, saya siap booking.")}">Chat WhatsApp</a><a class="button button-secondary" href="layanan.html#estimasi">Isi Estimator</a></div></div>
      <div class="contact-card" data-reveal><span class="contact-label">Jam operasional</span><h3>${escapeHtml(settings.hours_label)}</h3><p>Untuk area luar jam utama, admin bisa arahkan ke slot terdekat yang tersedia.</p></div>
      <div class="contact-card" data-reveal><span class="contact-label">Area layanan</span><h3>${escapeHtml(settings.area_label)}</h3><p>Untuk area lain, tetap bisa konsultasi dulu melalui WhatsApp agar admin cek biaya transport dan jadwal.</p></div>
      <div class="contact-card" data-reveal><span class="contact-label">Yang perlu dikirim saat chat</span><ul class="contact-list"><li>Foto objek yang ingin dibersihkan atau diperbaiki</li><li>Jumlah item atau pasangan sepatu</li><li>Lokasi dan jadwal yang diinginkan</li><li>Kondisi khusus seperti noda, bau, atau kerusakan</li></ul></div>
    </section>
  `;
}

function setupReveal() {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.16, rootMargin: "0px 0px -40px 0px" });
  document.querySelectorAll("[data-reveal]").forEach(element => observer.observe(element));
}

function setupEstimator() {
  const estimatorForm = document.querySelector("[data-estimator-form]");
  if (!estimatorForm) return;

  const totalNode = document.querySelector("[data-estimate-total]");
  const breakdownNode = document.querySelector("[data-estimate-breakdown]");
  const serviceSelect = estimatorForm.querySelector("[name='service']");
  const itemCountInput = estimatorForm.querySelector("[name='itemCount']");
  const areaSelect = estimatorForm.querySelector("[name='area']");
  const shoeTypeSelect = estimatorForm.querySelector("[name='shoeType']");
  const extras = Array.from(estimatorForm.querySelectorAll("input[name='extras']"));
  const phone = estimatorForm.dataset.phone || state.site.settings.phone_plain;
  const pricing = {};

  Array.from(serviceSelect.options).forEach(option => {
    pricing[option.value] = {
      label: option.dataset.title || option.textContent,
      base: Number(option.dataset.price || 0),
    };
  });

  const areaFees = { local: 0, west: 25000, jakarta: 50000, custom: 75000 };
  const shoeMultipliers = { standard: 1, premium: 1.35, repair: 1.7 };
  const extraFees = {
    deodorizer: { label: "Deodorizer", price: 25000 },
    antiBacterial: { label: "Anti-bacterial finish", price: 35000 },
    express: { label: "Priority slot", price: 50000 },
  };

  const syncEstimator = () => {
    const service = pricing[serviceSelect.value];
    const itemCount = Math.max(1, Number(itemCountInput.value || 1));
    const areaFee = areaFees[areaSelect.value] || 0;
    const selectedExtras = extras.filter(input => input.checked);
    let subtotal = service.base * itemCount;
    const notes = [`${service.label} x ${itemCount}`];

    if (serviceSelect.value === "shoe") {
      const multiplier = shoeMultipliers[shoeTypeSelect.value] || 1;
      subtotal = Math.round(subtotal * multiplier);
      notes.push(`Tipe sepatu: ${shoeTypeSelect.options[shoeTypeSelect.selectedIndex].text}`);
    }

    if (serviceSelect.value === "disinfectant") {
      notes.splice(0, notes.length, `${service.label} x 1 kunjungan`);
    }

    const extrasTotal = selectedExtras.reduce((sum, extra) => {
      notes.push(extraFees[extra.value].label);
      return sum + extraFees[extra.value].price;
    }, 0);

    const total = subtotal + extrasTotal + areaFee;
    totalNode.textContent = formatCurrency(total);
    breakdownNode.textContent = `Layanan: ${formatCurrency(subtotal)} / Tambahan: ${formatCurrency(extrasTotal)} / Area: ${formatCurrency(areaFee)} / Detail: ${notes.join(", ")}`;
  };

  serviceSelect.addEventListener("change", syncEstimator);
  itemCountInput.addEventListener("input", syncEstimator);
  areaSelect.addEventListener("change", syncEstimator);
  shoeTypeSelect.addEventListener("change", syncEstimator);
  extras.forEach(input => input.addEventListener("change", syncEstimator));

  estimatorForm.addEventListener("submit", event => {
    event.preventDefault();
    const formData = new FormData(estimatorForm);
    const selectedExtras = formData.getAll("extras").map(value => extraFees[value].label);
    const message = [
      "Halo Keep Clean, saya ingin booking layanan.",
      "",
      `Nama: ${formData.get("name") || "-"}`,
      `Layanan: ${pricing[formData.get("service")].label}`,
      `Jumlah item/pasang: ${formData.get("itemCount") || "1"}`,
      `Area: ${areaSelect.selectedOptions[0].text}`,
      `Jadwal yang diinginkan: ${formData.get("schedule") || "-"}`,
      `Tipe shoe care: ${serviceSelect.value === "shoe" ? shoeTypeSelect.selectedOptions[0].text : "-"}`,
      `Tambahan: ${selectedExtras.length ? selectedExtras.join(", ") : "-"}`,
      `Catatan: ${formData.get("details") || "-"}`,
      `Estimasi website: ${totalNode.textContent}`,
    ].join("\n");
    window.open(waLink(phone, message), "_blank");
  });

  syncEstimator();
}

async function init() {
  await loadSite();
  const page = document.body.dataset.page;
  const root = document.querySelector("[data-page-root]");
  renderHeader(page);
  renderFooter();
  hydrateMeta();

  if (page === "home") renderHome(root);
  if (page === "layanan") renderLayanan(root);
  if (page === "galeri") renderGaleri(root);
  if (page === "kontak") renderKontak(root);

  setupReveal();
  setupEstimator();
}

init().catch(error => {
  document.querySelector("[data-page-root]").innerHTML = `<section class="section"><div class="admin-alert admin-alert-error">Gagal memuat website: ${escapeHtml(error.message)}</div></section>`;
});
