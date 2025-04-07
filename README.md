<p align="center"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></p>

# Dash-Kospin

Aplikasi dashboard untuk manajemen Koperasi Simpan Pinjam (KoSPIN) berbasis Laravel dengan Filament sebagai admin panel.

## Deskripsi

Dash-Kospin adalah aplikasi web yang dirancang untuk membantu pengelolaan koperasi simpan pinjam. Aplikasi ini menangani berbagai aspek operasional koperasi seperti manajemen anggota, tabungan, pinjaman, deposito, dan lainnya.

## Fitur Utama

-   **Manajemen Anggota**: Pengelolaan data profil anggota koperasi
-   **Tabungan**: Pencatatan dan pengelolaan rekening tabungan anggota
-   **Pinjaman**: Pengelolaan pinjaman dengan berbagai jenis (Gadai, Kredit Elektronik, dll)
-   **Deposito**: Pengelolaan deposito anggota dengan perhitungan bunga otomatis
-   **Referral**: Sistem referensi anggota dan komisi
-   **Laporan**: Pembuatan laporan dalam format PDF
-   **Aktivitas Log**: Pencatatan aktivitas untuk audit trail
-   **API Service**: Layanan API untuk integrasi dengan aplikasi lain
-   **Dashboard Admin**: Panel admin yang komprehensif dengan Filament

## Teknologi

Aplikasi ini menggunakan teknologi:

-   **PHP 8.2+** dengan framework **Laravel 11**
-   **Filament 3** untuk admin panel
-   **TailwindCSS** untuk styling
-   **Shadcn UI** untuk komponen UI
-   **MySQL/MariaDB** untuk database
-   **Redis** untuk caching
-   **Laravel Octane** untuk performa tinggi
-   **Laravel Sanctum** untuk autentikasi API
-   **DOMPDF** untuk generasi laporan PDF

## Model Utama

Aplikasi ini menggunakan model-model berikut:

-   **User**: Model pengguna sistem
-   **Profile**: Data profil lengkap anggota koperasi
-   **Tabungan**: Rekening tabungan anggota
-   **Pinjaman**: Manajemen pinjaman
-   **Deposito**: Pengelolaan deposito anggota
-   **Gadai**: Produk pinjaman dengan jaminan
-   **KreditElektronik**: Produk kredit untuk pembelian elektronik
-   **CicilanEmas**: Produk cicilan untuk pembelian emas
-   **TransaksiTabungan**: Rekaman transaksi tabungan
-   **TransaksiPinjaman**: Rekaman transaksi pinjaman
-   **ProdukTabungan**: Jenis-jenis produk tabungan
-   **ProdukPinjaman**: Jenis-jenis produk pinjaman
-   **AnggotaReferral**: Data referral anggota
-   **TransaksiReferral**: Rekaman transaksi komisi referral

## Instalasi

### Prasyarat

-   PHP 8.2 atau lebih tinggi
-   Composer
-   Node.js dan NPM
-   Database MySQL/MariaDB
-   Redis (opsional, untuk caching)

### Langkah Instalasi

1. Clone repositori ini

    ```
    git clone <repo-url> dash-kospin
    cd dash-kospin
    ```

2. Install dependensi PHP

    ```
    composer install
    ```

3. Install dependensi JavaScript

    ```
    npm install
    ```

4. Salin file .env.example ke .env dan sesuaikan konfigurasi

    ```
    cp .env.example .env
    ```

5. Generate application key

    ```
    php artisan key:generate
    ```

6. Jalankan migrasi database

    ```
    php artisan migrate
    ```

7. Kompilasi aset frontend

    ```
    npm run build
    ```

8. Jalankan aplikasi
    ```
    php artisan serve
    ```

## Pengembangan

Untuk mode pengembangan, gunakan perintah:

```
composer dev
```

Perintah ini akan menjalankan server, antrian, log, dan vite secara bersamaan.

## Lisensi

Aplikasi ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
