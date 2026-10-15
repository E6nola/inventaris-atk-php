# Inventaris ATK (PHP & MySQL)

Dashboard inventaris perlengkapan dan alat tulis kantor (ATK) untuk pencatatan barang masuk, pengelolaan stok, dan kalkulasi nilai aset persediaan.

## Fitur Utama
- **Ringkasan Inventaris**: Statistik total produk, akumulasi stok barang, dan total nilai rupiah aset secara dinamis.
- **Manajemen Barang (CRUD)**: Input produk baru, pengunggahan foto fisik barang, update data, dan penghapusan produk.
- **Keamanan Data**: Menggunakan MySQL Prepared Statements untuk mitigasi celah SQL Injection serta sanitasi input form.
- **Autentikasi Session**: Akses dashboard dibatasi hanya untuk staf/admin yang telah login.

## Teknologi
- PHP
- MySQL / MariaDB
- Prepared Statements (mysqli)
- CSS Modern

## Panduan Instalasi
1. Pindahkan folder ke direktori server web (misal: htdocs/inventaris-atk-php).
2. Buat database inventaris di phpMyAdmin dan import berkas inventaris.sql.
3. Sesuaikan konfigurasi database jika diperlukan pada config/koneksi.php.
4. Jalankan melalui browser di:
   `
   http://localhost/inventaris-atk-php/
   `
"@
        gitignore = @"
.vscode/
.idea/
*.log
Thumbs.db
.DS_Store
