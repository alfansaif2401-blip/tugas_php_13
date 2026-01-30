# SISTAMIK - Sistem Akademik PETIK

Sistem Akademik Politeknik PETIK Jombang adalah aplikasi web untuk mengelola data mahasiswa dengan fitur CRUD lengkap.

## Fitur Utama

- ✅ Tambah data mahasiswa baru
- ✅ Edit data mahasiswa
- ✅ Hapus data mahasiswa
- ✅ Cari data mahasiswa
- ✅ Statistik mahasiswa
- ✅ Responsive design dengan UI modern

## Teknologi yang Digunakan

- PHP 7.4+
- MySQL/MariaDB
- PDO untuk koneksi database
- HTML5, CSS3, JavaScript
- Font Awesome untuk ikon

## Instalasi

1. Clone repository ini
2. Import database dari file `database.sql`
3. Konfigurasi koneksi database di `index.php`
4. Jalankan aplikasi di web server (XAMPP, WAMP, dll)

## Struktur Database

### Tabel mahasiswa
- id_mahasiswa (Primary Key)
- nim
- nama_lengkap
- jenis_kelamin
- tempat_lahir
- tanggal_lahir
- alamat
- no_telepon
- email
- id_jurusan (Foreign Key)
- angkatan
- status
- tanggal_masuk

### Tabel jurusan
- id_jurusan (Primary Key)
- nama_jurusan
- kode_jurusan

## Cara Penggunaan

1. Buka halaman utama aplikasi
2. Gunakan form untuk menambah data mahasiswa baru
3. Klik tombol Edit untuk mengubah data
4. Klik tombol Hapus untuk menghapus data
5. Gunakan fitur pencarian untuk mencari data

## Screenshot

![Dashboard SISTAMIK](https://via.placeholder.com/800x400?text=SISTAMIK+Dashboard)

## Kontribusi

Silakan fork repository ini dan buat pull request untuk kontribusi.

## Lisensi

MIT License - bebas digunakan untuk keperluan akademik dan komersial.
