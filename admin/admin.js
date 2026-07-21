const SITE_API_URL = "../includes/api/site.php";
const ADMIN_SESSION_API_URL = "../includes/api/admin/session.php";
const ADMIN_LOGIN_API_URL = "../includes/api/admin/login.php";
const ADMIN_LOGOUT_API_URL = "../includes/api/admin/logout.php";
const ADMIN_CONTENT_API_URL = "../includes/api/admin/content.php";
const ADMIN_PASSWORD_API_URL = "../includes/api/admin/password.php";
const ADMIN_UPLOAD_API_URL = "../includes/api/admin/upload.php";

const adminState = {
  site: null,
  admin: null,
};

const $ = selector => document.querySelector(selector);

const escapeHtml = value =>
  String(value).replace(/[&<>"']/g, char => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
  }[char]));

function showMessage(type, message) {
  const success = $("[data-admin-success]");
  const error = $("[data-admin-error]");
  success.classList.add("hidden");
  error.classList.add("hidden");
  if (!message) return;
  const target = type === "success" ? success : error;
  target.textContent = message;
  target.classList.remove("hidden");
}

function showLoginError(message) {
  const loginError = $("[data-login-error]");
  loginError.textContent = message;
  loginError.classList.remove("hidden");
}

function clearLoginError() {
  $("[data-login-error]").classList.add("hidden");
}

async function apiFetch(url, options = {}) {
  const response = await fetch(url, {
    credentials: "same-origin",
    headers: {
      ...(options.body instanceof FormData ? {} : { "Content-Type": "application/json" }),
      ...(options.headers || {}),
    },
    ...options,
  });

  const text = await response.text();
  let data = {};

  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    if (text.includes("<?php") || text.includes("declare(strict_types=1)")) {
      throw new Error("Admin panel backend PHP tidak berjalan. GitHub Pages tidak mendukung endpoint admin ini.");
    }

    throw new Error("Respons admin tidak valid.");
  }

  if (!response.ok) {
    throw new Error(data.message || "Request gagal.");
  }

  return data;
}

function toggleApp(isLoggedIn) {
  $("[data-admin-login]").classList.toggle("hidden", isLoggedIn);
  $("[data-admin-app]").classList.toggle("hidden", !isLoggedIn);
}

function renderStats() {
  $("[data-admin-stats]").innerHTML = `
    <article class="admin-stat-card"><span class="contact-label">Total paket</span><strong>${adminState.site.pricing.length}</strong></article>
    <article class="admin-stat-card"><span class="contact-label">Total galeri</span><strong>${adminState.site.gallery.length}</strong></article>
    <article class="admin-stat-card"><span class="contact-label">Total video</span><strong>${adminState.site.videos.length}</strong></article>
    <article class="admin-stat-card"><span class="contact-label">Nomor WA aktif</span><strong>${escapeHtml(adminState.site.settings.phone_display)}</strong></article>
  `;
}

function renderSettingsForm() {
  const form = $("[data-settings-form]");
  const fields = [
    ["site_name", "Nama situs"],
    ["tagline", "Tagline"],
    ["phone_display", "Nomor tampil"],
    ["phone_plain", "Nomor plain WA"],
    ["hero_title", "Hero title", true],
    ["hero_description", "Hero description", true],
    ["hero_primary_label", "Label CTA utama"],
    ["hero_secondary_label", "Label CTA kedua"],
    ["hero_primary_link", "Link CTA utama"],
    ["hero_secondary_link", "Link CTA kedua"],
    ["area_label", "Area layanan"],
    ["hours_label", "Jam operasional"],
    ["contact_email", "Email"],
    ["instagram", "Instagram"],
    ["footer_text", "Footer text", true],
    ["meta_description", "Meta description", true],
  ];

  form.innerHTML = `${fields.map(([key, label, large]) => `
    <div class="field ${large ? "field-full" : ""}">
      <label for="${key}">${label}</label>
      ${large
        ? `<textarea id="${key}" name="${key}" rows="4">${escapeHtml(adminState.site.settings[key] || "")}</textarea>`
        : `<input id="${key}" name="${key}" type="text" value="${escapeHtml(adminState.site.settings[key] || "")}">`}
    </div>
  `).join("")}
  <div class="button-row field-full"><button class="button button-primary" type="submit">Simpan Pengaturan</button></div>`;

  form.onsubmit = async event => {
    event.preventDefault();
    const fd = new FormData(form);
    fields.forEach(([key]) => {
      adminState.site.settings[key] = String(fd.get(key) || "").trim();
    });
    await saveSite("Update website settings");
  };
}

function renderPricingForm() {
  const form = $("[data-pricing-form]");
  form.innerHTML = `${adminState.site.pricing.map((item, index) => `
    <article class="admin-card">
      <div class="admin-form-grid">
        <div class="field"><label>Badge</label><input name="badge_${index}" type="text" value="${escapeHtml(item.badge)}"></div>
        <div class="field"><label>Title</label><input name="title_${index}" type="text" value="${escapeHtml(item.title)}"></div>
        <div class="field"><label>Price label</label><input name="price_label_${index}" type="text" value="${escapeHtml(item.price_label)}"></div>
        <div class="field"><label>Price value</label><input name="price_value_${index}" type="number" value="${item.price_value}"></div>
        <div class="field field-full"><label>Description</label><textarea name="description_${index}" rows="3">${escapeHtml(item.description)}</textarea></div>
        <div class="field field-full"><label>Features (satu baris satu item)</label><textarea name="features_${index}" rows="4">${escapeHtml(item.features.join("\n"))}</textarea></div>
      </div>
    </article>
  `).join("")}
  <div class="button-row"><button class="button button-primary" type="submit">Simpan Harga</button></div>`;

  form.onsubmit = async event => {
    event.preventDefault();
    const fd = new FormData(form);
    adminState.site.pricing = adminState.site.pricing.map((item, index) => ({
      ...item,
      badge: String(fd.get(`badge_${index}`) || "").trim(),
      title: String(fd.get(`title_${index}`) || "").trim(),
      price_label: String(fd.get(`price_label_${index}`) || "").trim(),
      price_value: Number(fd.get(`price_value_${index}`) || 0),
      description: String(fd.get(`description_${index}`) || "").trim(),
      features: String(fd.get(`features_${index}`) || "").split("\n").map(text => text.trim()).filter(Boolean),
    }));
    await saveSite("Update pricing");
  };
}

function renderGalleryList() {
  const wrap = $("[data-gallery-list]");
  wrap.innerHTML = adminState.site.gallery.map((item, index) => `
    <article class="admin-card admin-gallery-row">
      <img src="../${escapeHtml(item.path)}" alt="${escapeHtml(item.title)}" class="admin-thumb">
      <form class="admin-form-grid" data-gallery-form="${index}">
        <div class="field"><label>Judul gambar</label><input name="title" type="text" value="${escapeHtml(item.title)}"></div>
        <div class="field"><label>Upload gambar baru</label><input name="file" type="file" accept=".jpg,.jpeg,.png,.webp,.svg"></div>
        <div class="button-row field-full">
          <button class="button button-primary" type="submit">Simpan Perubahan</button>
          <button class="button button-danger" type="button" data-gallery-delete="${index}">Hapus</button>
        </div>
      </form>
    </article>
  `).join("");

  wrap.querySelectorAll("[data-gallery-form]").forEach(form => {
    form.onsubmit = async event => {
      event.preventDefault();
      const index = Number(form.dataset.galleryForm);
      const fd = new FormData(form);
      adminState.site.gallery[index].title = String(fd.get("title") || "").trim();
      const file = fd.get("file");
      if (file instanceof File && file.size) {
        adminState.site.gallery[index].path = await uploadFile(file, "gallery");
      }
      await saveSite("Update gallery item");
    };
  });

  wrap.querySelectorAll("[data-gallery-delete]").forEach(button => {
    button.onclick = async () => {
      const index = Number(button.dataset.galleryDelete);
      adminState.site.gallery.splice(index, 1);
      await saveSite("Delete gallery item");
    };
  });
}

function renderVideoList() {
  const wrap = $("[data-video-list]");
  wrap.innerHTML = adminState.site.videos.map((item, index) => `
    <article class="admin-card admin-gallery-row">
      <video class="admin-thumb" controls poster="../${escapeHtml(item.poster)}"><source src="../${escapeHtml(item.path)}" type="video/mp4"></video>
      <form class="admin-form-grid" data-video-form="${index}">
        <div class="field"><label>Judul video</label><input name="title" type="text" value="${escapeHtml(item.title)}"></div>
        <div class="field"><label>Upload video baru</label><input name="video" type="file" accept=".mp4,.webm,.mov"></div>
        <div class="field field-full"><label>Upload poster baru</label><input name="poster" type="file" accept=".jpg,.jpeg,.png,.webp"></div>
        <div class="button-row field-full">
          <button class="button button-primary" type="submit">Simpan Perubahan</button>
          <button class="button button-danger" type="button" data-video-delete="${index}">Hapus</button>
        </div>
      </form>
    </article>
  `).join("");

  wrap.querySelectorAll("[data-video-form]").forEach(form => {
    form.onsubmit = async event => {
      event.preventDefault();
      const index = Number(form.dataset.videoForm);
      const fd = new FormData(form);
      adminState.site.videos[index].title = String(fd.get("title") || "").trim();
      const video = fd.get("video");
      const poster = fd.get("poster");
      if (video instanceof File && video.size) adminState.site.videos[index].path = await uploadFile(video, "video");
      if (poster instanceof File && poster.size) adminState.site.videos[index].poster = await uploadFile(poster, "poster");
      await saveSite("Update video item");
    };
  });

  wrap.querySelectorAll("[data-video-delete]").forEach(button => {
    button.onclick = async () => {
      const index = Number(button.dataset.videoDelete);
      adminState.site.videos.splice(index, 1);
      await saveSite("Delete video item");
    };
  });
}

function renderBrandingForm() {
  const form = $("[data-branding-form]");
  form.logo_text.value = adminState.site.branding.logo_text || "";
  $("[data-brand-logo]").src = `../${adminState.site.branding.logo_path}`;

  form.onsubmit = async event => {
    event.preventDefault();
    adminState.site.branding.logo_text = form.logo_text.value.trim();
    const file = form.logo_file.files[0];
    if (file) {
      adminState.site.branding.logo_path = await uploadFile(file, "branding");
      adminState.site.branding.logo_type = "upload";
    }
    await saveSite("Update branding");
  };
}

function bindAddForms() {
  $("[data-gallery-add-form]").onsubmit = async event => {
    event.preventDefault();
    const fd = new FormData(event.currentTarget);
    const file = fd.get("file");
    const title = String(fd.get("title") || "").trim();
    if (!title || !(file instanceof File) || !file.size) {
      throw new Error("Judul dan file gambar wajib diisi.");
    }

    const path = await uploadFile(file, "gallery");
    adminState.site.gallery.push({ id: `g_${Date.now()}`, title, path, type: "image", featured: false });
    await saveSite("Add gallery item");
    event.currentTarget.reset();
  };

  $("[data-video-add-form]").onsubmit = async event => {
    event.preventDefault();
    const fd = new FormData(event.currentTarget);
    const title = String(fd.get("title") || "").trim();
    const video = fd.get("video");
    const poster = fd.get("poster");
    if (!title || !(video instanceof File) || !video.size || !(poster instanceof File) || !poster.size) {
      throw new Error("Judul, video, dan poster wajib diisi.");
    }

    const videoPath = await uploadFile(video, "video");
    const posterPath = await uploadFile(poster, "poster");
    adminState.site.videos.push({ id: `v_${Date.now()}`, title, path: videoPath, poster: posterPath });
    await saveSite("Add video item");
    event.currentTarget.reset();
  };
}

function bindPasswordForm() {
  $("[data-password-form]").onsubmit = async event => {
    event.preventDefault();
    const fd = new FormData(event.currentTarget);
    const currentPassword = String(fd.get("current_password") || "");
    const newPassword = String(fd.get("new_password") || "");
    const confirmPassword = String(fd.get("confirm_password") || "");
    if (newPassword !== confirmPassword) throw new Error("Konfirmasi password baru tidak sama.");
    if (newPassword.length < 8) throw new Error("Password baru minimal 8 karakter.");

    await apiFetch(ADMIN_PASSWORD_API_URL, {
      method: "POST",
      body: JSON.stringify({
        current_password: currentPassword,
        new_password: newPassword,
        confirm_password: confirmPassword,
      }),
    });

    event.currentTarget.reset();
    showMessage("success", "Password admin berhasil diubah.");
  };
}

async function uploadFile(file, type) {
  const body = new FormData();
  body.append("type", type);
  body.append("file", file);
  const data = await apiFetch(ADMIN_UPLOAD_API_URL, {
    method: "POST",
    body,
  });
  return data.path;
}

async function loadSite() {
  adminState.site = await apiFetch(SITE_API_URL);
}

async function saveSite(message) {
  const data = await apiFetch(ADMIN_CONTENT_API_URL, {
    method: "POST",
    body: JSON.stringify({ site: adminState.site }),
  });
  adminState.site = data.site;
  renderAll();
  showMessage("success", `${message} berhasil.`);
}

function renderAll() {
  renderStats();
  renderSettingsForm();
  renderPricingForm();
  renderGalleryList();
  renderVideoList();
  renderBrandingForm();
  bindAddForms();
  bindPasswordForm();
}

async function handleLogin(event) {
  event.preventDefault();
  clearLoginError();
  const fd = new FormData(event.currentTarget);

  try {
    const data = await apiFetch(ADMIN_LOGIN_API_URL, {
      method: "POST",
      body: JSON.stringify({
        username: String(fd.get("username") || "").trim(),
        password: String(fd.get("password") || ""),
      }),
    });

    adminState.admin = data.admin;
    await loadSite();
    toggleApp(true);
    renderAll();
    event.currentTarget.reset();
  } catch (error) {
    showLoginError(error.message);
  }
}

async function initAdmin() {
  $("[data-login-form]").addEventListener("submit", handleLogin);
  $("[data-logout]").addEventListener("click", async () => {
    await apiFetch(ADMIN_LOGOUT_API_URL, { method: "POST", body: "{}" });
    adminState.admin = null;
    toggleApp(false);
    showMessage("success", "");
  });

  const session = await apiFetch(ADMIN_SESSION_API_URL);
  if (!session.authenticated) {
    toggleApp(false);
    await loadSite();
    return;
  }

  adminState.admin = session.admin;
  await loadSite();
  toggleApp(true);
  renderAll();
}

initAdmin().catch(error => {
  toggleApp(false);
  showLoginError(error.message);
});
