# Aplikasi Buana Group — Starter MVP

Aplikasi web internal berbasis PHP Native + MySQL untuk dijalankan di XAMPP dan dikembangkan melalui Visual Studio Code.

## Modul yang tersedia
- Dashboard ringkas seluruh unit usaha
- Katalog lima unit: Air Mineral, Food Services, Tour Travel, Ticketing, Konveksi
- Pembuatan pesanan dan daftar pesanan
- Alur status: Menunggu → Disetujui → Diproses → Siap → Selesai / Dibatalkan
- Pembuatan invoice otomatis saat pesanan disetujui
- Pencatatan pembayaran bertahap dan status lunas
- Data pelanggan, laporan transaksi sederhana
- Manajemen produk dan akun pengguna (khusus Super Admin)
- Hak akses dasar dan log aktivitas

## Persyaratan
- XAMPP dengan PHP 8.0+ dan MySQL/MariaDB
- Browser modern
- Visual Studio Code (opsional untuk mengedit kode)

## Cara instalasi di Windows
1. Ekstrak folder `buana_group_app` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, pilih file `database/buana_group.sql`, lalu klik **Import**.
   - SQL juga membuat database `buana_group` otomatis.
5. Buka `config.php`, sesuaikan DB_HOST, DB_NAME, DB_USER, DB_PASS bila konfigurasi XAMPP berbeda.
6. Buka `http://localhost/buana_group_app/login.php`.

## Akun demo
- Super Admin: `admin@buana.local`
- Password: `password`
- Pelanggan demo: `pelanggan@buana.local`
- Password: `password`

**Penting:** segera ubah password akun demo sebelum aplikasi dipakai untuk data sungguhan. Akun pelanggan demo hanya untuk uji alur pelanggan; halaman login menggunakan akun yang ada pada tabel `users`.

## Cara custom
- Ubah nama, deskripsi unit dan produk melalui tabel `business_units` dan `products` di phpMyAdmin.
- Tambah produk dari menu **Produk & Unit** setelah login Super Admin.
- Tambah akun dari menu **Pengguna**.
- Ubah tampilan pada `assets/style.css`.
- Ubah alur aplikasi pada file PHP di folder utama.
- Struktur database ada pada `database/buana_group.sql`.

## Catatan batasan versi awal
Versi ini adalah fondasi MVP lokal, bukan sistem produksi. Belum termasuk:
- Integrasi SSO UNWAHAS, payment gateway/QRIS/VA sungguhan, API tiket, live tracking armada, barcode boarding pass.
- Upload KTP/paspor, unggah desain konveksi, manajemen ukuran massal, tahapan produksi rinci.
- Poin loyalitas, cicilan/paylater, notifikasi WhatsApp/email otomatis.
- Pengelolaan vendor dan biaya aktual, kalkulasi margin per pesanan, rekonsiliasi bank, ekspor akuntansi.
- Limit kredit otomatis dan pemblokiran pesanan tempo sesuai kebijakan institusi.

Sesuai konsep presentasi, fitur integrasi berisiko/kompleks sebaiknya dibangun setelah pilot dan validasi operasional. Nilai harga produk yang disediakan adalah data contoh, bukan harga resmi Buana Group.

## Keamanan sebelum online
- Ganti password default dan gunakan password unik.
- Buat user database khusus dengan hak akses terbatas (jangan gunakan root).
- Aktifkan HTTPS, backup terjadwal, validasi upload, rate limiting, MFA admin.
- Lakukan audit hak akses dan pengujian keamanan sebelum memuat data pelanggan.
