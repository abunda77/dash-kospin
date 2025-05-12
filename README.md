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

## Detail Teknologi dan Framework

### Laravel 11

Laravel 11 adalah framework PHP modern dengan pendekatan yang lebih ramping dan syntax yang elegan. Beberapa fitur utama yang digunakan dalam aplikasi ini:

-   **Struktur Aplikasi Minimalis**: Laravel 11 menghadirkan struktur aplikasi yang lebih ramping dengan file konfigurasi yang lebih sedikit.
-   **Health Check Route**: Fitur bawaan untuk pemantauan kesehatan aplikasi di endpoint `/up`.
-   **Eloquent ORM**: Model relasional yang kuat untuk interaksi dengan database yang intuitif dan ekspresif.
-   **Middleware**: Untuk filter dan transformasi HTTP request ke aplikasi.
-   **Blade Templating**: Sistem template yang intuitif dan kuat untuk tampilan.
-   **Migrasi Database**: Kontrol versi untuk skema database Anda.
-   **Per-Second Rate Limiting**: Pembatasan rate yang lebih granular dalam hitungan detik.
-   **Queue & Jobs**: Pemrosesan tugas latar belakang untuk menangani operasi yang membutuhkan waktu.
-   **Events & Listeners**: Arsitektur event-driven untuk membuat aplikasi yang fleksibel.
-   **File Storage**: Abstraksi penyimpanan file yang mudah untuk berbagai driver penyimpanan.

### Filament 3

Filament 3 adalah admin panel framework modern untuk Laravel yang kami gunakan untuk membuat antarmuka admin yang kuat dan intuitif:

-   **Panel Builder**: Membuat panel admin yang kaya fitur dengan cepat, dengan CRUD dan operasi lanjutan.
-   **Form Builder**: Buat formulir yang kuat dengan validasi dan berbagai jenis input.
-   **Table Builder**: Tabel data interaktif dengan filter, pencarian, dan pagination.
-   **Actions**: Aksi yang dapat dikonfigurasi untuk menangani operasi umum dan kustom.
-   **Widgets**: Komponen dashboard modular untuk visualisasi data.
-   **Navigasi**: Navigasi yang fleksibel dan dapat disesuaikan.
-   **Notifications**: Notifikasi real-time dalam aplikasi.
-   **Resources**: Mengelola model CRUD dengan cepat dan mudah.
-   **Multi-tenancy**: Dukungan untuk aplikasi multi-tenant.
-   **Globalization**: Dukungan untuk multi-bahasa dan lokalisasi.

### TailwindCSS

TailwindCSS adalah framework CSS utility-first yang kami gunakan untuk styling aplikasi:

-   **Utility-First**: Pendekatan styling langsung di HTML tanpa menulis CSS kustom.
-   **Just-in-time Engine**: Kompilasi CSS yang sangat cepat dan hanya menghasilkan class yang digunakan.
-   **Responsive Design**: Desain responsif dengan prefiks breakpoint mudah (`sm:`, `md:`, `lg:`, `xl:`, `2xl:`).
-   **Dark Mode**: Dukungan dark mode bawaan.
-   **Customization**: Konfigurasi tema, warna, dan spacing sesuai kebutuhan proyek.
-   **State Variants**: Styling untuk berbagai state (`hover:`, `focus:`, `active:`, dll).
-   **Plugins**: Memperluas Tailwind dengan plugin untuk kebutuhan khusus.
-   **Preflight**: Reset CSS yang elegan untuk konsistensi cross-browser.
-   **Grid & Flexbox**: Utilitas Layout yang kuat.
-   **Typography Plugin**: Styling tipografi yang anggun.

### Shadcn UI

Shadcn UI adalah koleksi komponen UI yang dapat digunakan kembali yang dibangun dengan Tailwind CSS:

-   **Komponen Siap Pakai**: Aksesibilitas, responsif, dan dapat disesuaikan.
-   **Copy & Paste**: Komponen digabungkan langsung ke dalam proyek, bukan sebagai dependensi.
-   **Kontrol Penuh**: Kode komponen sepenuhnya milik pengembang untuk dikustomisasi.
-   **Aksesibilitas**: Komponen dirancang dengan aksesibilitas sebagai prioritas.
-   **Theming**: Dukungan tema yang mudah dikustomisasi.
-   **Styling yang Konsisten**: Desain dan nuansa visual yang konsisten di seluruh komponen.
-   **TypeScript Support**: Komponen dengan dukungan TypeScript untuk pengembangan yang lebih aman.
-   **Primitif Headless**: Menggunakan Radix UI primitives untuk layar aksesibilitas, keyboard, dan fokus.

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
