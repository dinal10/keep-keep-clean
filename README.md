# Keepkeepclean Website + Admin Panel

Website ini sekarang berbasis PHP ringan dengan panel admin sederhana.

## Halaman utama

- `index.php`: homepage
- `layanan.php`: paket harga dan estimator WhatsApp
- `galeri.php`: hasil before/after dan video
- `kontak.php`: halaman closing dan kontak

## Admin panel

- URL login: `/admin/login.php`
- Default login:
  - Username: `admin`
  - Password: `keepclean123`

Segera ganti password default setelah deploy.

## Yang bisa diupdate dari admin

- Teks website utama
- Nomor WhatsApp
- Harga dan isi paket layanan
- Judul dan gambar galeri
- Tambah, edit, hapus video
- Logo / branding
- Password admin

## Struktur penting

- `data/site.json`: sumber data website
- `data/users.json`: akun admin
- `uploads/gallery/`: gambar galeri hasil upload
- `uploads/branding/`: logo hasil upload
- `uploads/videos/`: video hasil upload
- `assets/`: aset awal foto, video, dan logo SVG default

## Cara deploy ke hosting PHP

1. Upload semua file dan folder ke `public_html/` atau root domain.
2. Pastikan hosting mendukung PHP 8.x.
3. Biarkan file `.htaccess` tetap ada agar `index.php` jadi halaman utama.
4. Pastikan folder `uploads/` bisa ditulis oleh server.
5. Login ke `/admin/login.php` lalu update data sesuai kebutuhan.

## Catatan

- Semua CTA WhatsApp membaca nomor dari `data/site.json`.
- Estimator memberi kisaran harga awal, bukan invoice final.
- Versi aktif hanya file `.php`. File `.html` lama sudah dibersihkan agar tidak membingungkan.
