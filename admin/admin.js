const SITE_DATA_URL = "../data/site.json";
const USERS_DATA_URL = "../data/users.json";
const ADMIN_STORAGE_KEY = "keepkeepclean_admin_session";
const GITHUB_STORAGE_KEY = "keepkeepclean_github_config";

const adminState = {
  site: null,
  users: null,
  session: null,
  github: null,
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

async function sha256(text) {
  const data = new TextEncoder().encode(text);
  const hash = await crypto.subtle.digest("SHA-256", data);
  return Array.from(new Uint8Array(hash)).map(byte => byte.toString(16).padStart(2, "0")).join("");
}

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

async function loadJson(url) {
  const response = await fetch(url, { cache: "no-store" });
  if (!response.ok) throw new Error(`Gagal memuat ${url}`);
  return response.json();
}

function loadSavedGithubConfig() {
  const raw = localStorage.getItem(GITHUB_STORAGE_KEY);
  return raw ? JSON.parse(raw) : { owner: "dinal10", repo: "keep-keep-clean", branch: "main", token: "" };
}

function saveGithubConfig(config) {
  localStorage.setItem(GITHUB_STORAGE_KEY, JSON.stringify(config));
  adminState.github = config;
}

function setSession(username) {
  localStorage.setItem(ADMIN_STORAGE_KEY, JSON.stringify({ username, ts: Date.now() }));
  adminState.session = { username };
}

function clearSession() {
  localStorage.removeItem(ADMIN_STORAGE_KEY);
  adminState.session = null;
}

function getSession() {
  const raw = localStorage.getItem(ADMIN_STORAGE_KEY);
  return raw ? JSON.parse(raw) : null;
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
    await commitSiteData("Update website settings");
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
      features: String(fd.get(`features_${index}`) || "").split("\n").map(itemText => itemText.trim()).filter(Boolean),
    }));
    await commitSiteData("Update pricing");
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
      if (file && file.size) {
        adminState.site.gallery[index].path = await uploadFile(file, "uploads/gallery", "gallery");
      }
      await commitSiteData("Update gallery item");
    };
  });

  wrap.querySelectorAll("[data-gallery-delete]").forEach(button => {
    button.onclick = async () => {
      const index = Number(button.dataset.galleryDelete);
      adminState.site.gallery.splice(index, 1);
      await commitSiteData("Delete gallery item");
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
      if (video && video.size) adminState.site.videos[index].path = await uploadFile(video, "uploads/videos", "video");
      if (poster && poster.size) adminState.site.videos[index].poster = await uploadFile(poster, "uploads/gallery", "poster");
      await commitSiteData("Update video item");
    };
  });

  wrap.querySelectorAll("[data-video-delete]").forEach(button => {
    button.onclick = async () => {
      const index = Number(button.dataset.videoDelete);
      adminState.site.videos.splice(index, 1);
      await commitSiteData("Delete video item");
    };
  });
}

function renderBrandingForm() {
  const form = $("[data-branding-form]");
  form.logo_text.value = adminState.site.branding.logo_text;
  $("[data-brand-logo]").src = `../${adminState.site.branding.logo_path}`;

  form.onsubmit = async event => {
    event.preventDefault();
    adminState.site.branding.logo_text = form.logo_text.value.trim();
    const file = form.logo_file.files[0];
    if (file) {
      adminState.site.branding.logo_path = await uploadFile(file, "uploads/branding", "logo");
      adminState.site.branding.logo_type = "upload";
    }
    await commitSiteData("Update branding");
  };
}

function bindAddForms() {
  $("[data-gallery-add-form]").onsubmit = async event => {
    event.preventDefault();
    const fd = new FormData(event.currentTarget);
    const file = fd.get("file");
    const title = String(fd.get("title") || "").trim();
    if (!title || !file || !file.size) throw new Error("Judul dan file gambar wajib diisi.");
    const path = await uploadFile(file, "uploads/gallery", "gallery");
    adminState.site.gallery.push({ id: `g_${Date.now()}`, title, path, type: "image", featured: false });
    await commitSiteData("Add gallery item");
  };

  $("[data-video-add-form]").onsubmit = async event => {
    event.preventDefault();
    const fd = new FormData(event.currentTarget);
    const title = String(fd.get("title") || "").trim();
    const video = fd.get("video");
    const poster = fd.get("poster");
    if (!title || !video || !video.size || !poster || !poster.size) throw new Error("Judul, video, dan poster wajib diisi.");
    const videoPath = await uploadFile(video, "uploads/videos", "video");
    const posterPath = await uploadFile(poster, "uploads/gallery", "poster");
    adminState.site.videos.push({ id: `v_${Date.now()}`, title, path: videoPath, poster: posterPath });
    await commitSiteData("Add video item");
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

    const currentHash = await sha256(currentPassword);
    if (currentHash !== adminState.users.admin.password_sha256) throw new Error("Password lama tidak cocok.");

    adminState.users.admin.password_sha256 = await sha256(newPassword);
    await commitUsersData("Change admin password");
    event.currentTarget.reset();
  };
}

function populateGithubForm() {
  const form = $("[data-github-form]");
  const config = loadSavedGithubConfig();
  adminState.github = config;
  form.owner.value = config.owner || "dinal10";
  form.repo.value = config.repo || "keep-keep-clean";
  form.branch.value = config.branch || "main";
  form.token.value = config.token || "";
  form.onsubmit = event => {
    event.preventDefault();
    const fd = new FormData(form);
    saveGithubConfig({
      owner: String(fd.get("owner") || "").trim(),
      repo: String(fd.get("repo") || "").trim(),
      branch: String(fd.get("branch") || "").trim(),
      token: String(fd.get("token") || "").trim(),
    });
    showMessage("success", "Koneksi GitHub disimpan di browser ini.");
  };
}

async function fileToBase64(file) {
  const buffer = await file.arrayBuffer();
  let binary = "";
  const bytes = new Uint8Array(buffer);
  bytes.forEach(byte => {
    binary += String.fromCharCode(byte);
  });
  return btoa(binary);
}

function githubHeaders() {
  if (!adminState.github?.token) throw new Error("GitHub token belum diisi.");
  return {
    Authorization: `Bearer ${adminState.github.token}`,
    Accept: "application/vnd.github+json",
    "X-GitHub-Api-Version": "2022-11-28",
  };
}

async function githubGetContent(path) {
  const { owner, repo, branch } = adminState.github;
  const response = await fetch(`https://api.github.com/repos/${owner}/${repo}/contents/${path}?ref=${encodeURIComponent(branch)}`, {
    headers: githubHeaders(),
  });
  if (response.status === 404) return null;
  if (!response.ok) throw new Error(`Gagal membaca ${path} di GitHub.`);
  return response.json();
}

async function githubPutContent(path, contentBase64, message) {
  const { owner, repo, branch } = adminState.github;
  const current = await githubGetContent(path);
  const body = {
    message,
    content: contentBase64,
    branch,
  };
  if (current?.sha) body.sha = current.sha;
  const response = await fetch(`https://api.github.com/repos/${owner}/${repo}/contents/${path}`, {
    method: "PUT",
    headers: {
      ...githubHeaders(),
      "Content-Type": "application/json",
    },
    body: JSON.stringify(body),
  });
  if (!response.ok) {
    const data = await response.json().catch(() => ({}));
    throw new Error(data.message || `Gagal menulis ${path} ke GitHub.`);
  }
}

async function uploadFile(file, folder, prefix) {
  const extension = file.name.split(".").pop().toLowerCase();
  const name = `${prefix}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}.${extension}`;
  const path = `${folder}/${name}`;
  await githubPutContent(path, await fileToBase64(file), `Upload ${path}`);
  return path;
}

async function commitSiteData(message) {
  await githubPutContent("data/site.json", btoa(unescape(encodeURIComponent(JSON.stringify(adminState.site, null, 2)))), message);
  renderAll();
  showMessage("success", `${message} berhasil.`);
}

async function commitUsersData(message) {
  await githubPutContent("data/users.json", btoa(unescape(encodeURIComponent(JSON.stringify(adminState.users, null, 2)))), message);
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
  const fd = new FormData(event.currentTarget);
  const username = String(fd.get("username") || "").trim();
  const password = String(fd.get("password") || "");
  const loginError = $("[data-login-error]");
  loginError.classList.add("hidden");

  const user = adminState.users[username];
  if (!user) {
    loginError.textContent = "Username tidak ditemukan.";
    loginError.classList.remove("hidden");
    return;
  }

  const passwordHash = await sha256(password);
  if (passwordHash !== user.password_sha256) {
    loginError.textContent = "Password salah.";
    loginError.classList.remove("hidden");
    return;
  }

  setSession(username);
  toggleApp(true);
  populateGithubForm();
  renderAll();
}

async function initAdmin() {
  adminState.site = await loadJson(SITE_DATA_URL);
  adminState.users = await loadJson(USERS_DATA_URL);
  adminState.session = getSession();

  $("[data-login-form]").addEventListener("submit", handleLogin);
  $("[data-logout]").addEventListener("click", () => {
    clearSession();
    toggleApp(false);
  });

  if (adminState.session?.username && adminState.users[adminState.session.username]) {
    toggleApp(true);
    populateGithubForm();
    renderAll();
  } else {
    toggleApp(false);
  }
}

initAdmin().catch(error => {
  alert(`Gagal memuat admin panel: ${error.message}`);
});
