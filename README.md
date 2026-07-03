# Keep Clean Website + Admin Panel for GitHub Pages

Website ini sudah diubah agar kompatibel dengan GitHub Pages tanpa kehilangan requirement utama.

## Halaman utama

- `index.html`: homepage
- `layanan.html`: paket harga dan estimator WhatsApp
- `galeri.html`: hasil before/after dan video
- `kontak.html`: halaman closing dan kontak

## Admin panel

- URL login: `/admin/index.html`
- Default login:
  - Username: `admin`
  - Password: `keepclean123`

Setelah login, admin perlu memasukkan:

- GitHub owner
- nama repository
- branch
- GitHub Personal Access Token / Fine-grained token

Token dipakai untuk menulis perubahan langsung ke repository melalui GitHub API.

## Yang bisa diupdate dari admin

- Teks website utama
- Nomor WhatsApp
- Harga dan isi paket layanan
- Tambah, edit, hapus galeri
- Tambah, edit, hapus video
- Logo / branding
- Password admin

## Struktur penting

- `data/site.json`: sumber data website publik
- `data/users.json`: akun admin untuk login panel
- `assets/`: aset awal foto, video, dan logo SVG default
- `uploads/gallery/`: target upload gambar baru di repo
- `uploads/branding/`: target upload logo baru di repo
- `uploads/videos/`: target upload video baru di repo

Folder `uploads/` tidak perlu ada lebih dulu. Admin akan membuat file baru ke path tersebut lewat GitHub API saat upload.

## Cara deploy ke GitHub Pages

1. Push semua file ke repository GitHub.
2. Buka `Settings > Pages`.
3. Pilih source: `Deploy from a branch`.
4. Pilih branch `main` dan folder `/root`.
5. Simpan, lalu tunggu build selesai.
6. Buka website dari URL GitHub Pages.
7. Login ke `/admin/index.html`.
8. Masukkan GitHub token yang punya izin `Contents: Read and write`.

## Catatan penting

- Semua CTA WhatsApp membaca nomor dari `data/site.json`.
- Estimator memberi kisaran harga awal, bukan invoice final.
- GitHub Pages tidak menjalankan PHP, jadi project sekarang full HTML/CSS/JS.
- Login admin di GitHub Pages bersifat client-side. Untuk write access yang sebenarnya, keamanan utamanya tetap berasal dari GitHub token Anda.
