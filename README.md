<p align="center"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></p>

# Dash-Kospin

Aplikasi dashboard untuk manajemen Koperasi Simpan Pinjam (KoSPIN) berbasis Laravel dengan Filament sebagai admin panel.

## Deskripsi

Dash-Kospin adalah aplikasi web yang dirancang untuk membantu pengelolaan koperasi simpan pinjam. Aplikasi ini menangani berbagai aspek operasional koperasi seperti manajemen anggota, tabungan, pinjaman, deposito, dan lainnya.

## Fitur Utama

-   **Manajemen Anggota**: Pengelolaan data profil anggota koperasi
-   **Tabungan**: Pencatatan dan pengelolaan rekening tabungan anggota
-   **Pinjaman**: Pengelolaan pinjaman dengan berbagai jenis (Gadai, Kredit Elektronik, dll)
-   **Pelunasan**: Pencatatan dan pengelolaan pelunasan pinjaman anggota
-   **Deposito**: Pengelolaan deposito anggota dengan perhitungan bunga otomatis
-   **Referral**: Sistem referensi anggota dan komisi
-   **Laporan**: Pembuatan laporan dalam format PDF
-   **QR Code Barcode**: Cetak barcode QR Code untuk rekening tabungan dengan scan public access
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
-   **QRServer API** untuk generate QR Code barcode

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

### Resources Laravel 11

Resources di Laravel 11 adalah fitur yang memudahkan pembuatan API dan transformasi model/data ke format JSON. Beberapa hal penting tentang Resources:

-   **Transformasi Model**: Ubah model Eloquent atau koleksi menjadi JSON dengan mudah
-   **Kontrol Data**: Secara selektif menyertakan atribut dalam respons JSON
-   **Nesting Resource**: Menyertakan relasi (nested resources) dalam respons
-   **Resource Collections**: Membuat resource khusus untuk koleksi model
-   **Data Wrapping**: Membungkus respons dalam struktur data tertentu
-   **Pagination**: Dukungan bawaan untuk merespons data yang dihalaman
-   **Conditional Attributes**: Menyertakan atribut berdasarkan kondisi tertentu
-   **Conditional Relationships**: Menyertakan relasi berdasarkan kondisi
-   **Metadata Tambahan**: Menambahkan metadata di respons API
-   **Resource Response**: Mengkustomisasi status kode HTTP dan header

Untuk dokumentasi lengkap tentang Resource Laravel 11, kunjungi [Dokumentasi Resmi Laravel](https://laravel.com/docs/11.x/eloquent-resources).

### Filament 3

Filament 3 adalah admin panel framework modern untuk Laravel yang kami gunakan untuk membuat antarmuka admin yang kuat dan intuitif:

-   **Panel Builder**: Membuat panel admin yang kaya fitur dengan cepat, dengan CRUD dan operasi lanjutan.
-   **Form Builder**: Buat formulir yang kuat dengan validasi dan berbagai jenis input yang mencakup lebih dari 25 komponen bawaan.
-   **Table Builder**: Tabel data interaktif dengan filter, pencarian, pagination, dan kemampuan ekspor data.
-   **Actions**: Aksi yang dapat dikonfigurasi dalam bentuk modal dan slide-over untuk menangani operasi CRUD dan kustom.
-   **Widgets**: Komponen dashboard modular untuk visualisasi data dengan grafik dan statistik real-time.
-   **Navigation**: Navigasi yang fleksibel dan dapat disesuaikan dengan dukungan untuk grup, ikon, dan badge.
-   **Notifications**: Notifikasi real-time dalam aplikasi menggunakan Livewire.
-   **Resources**: Kelas statis untuk membangun antarmuka CRUD dengan mudah berdasarkan model Eloquent.
-   **Infolist Builder**: Menampilkan informasi read-only kepada pengguna tentang record tertentu dengan tata letak yang fleksibel.
-   **Global Search**: Pencarian cepat untuk semua model melalui panel admin.
-   **Relationship Management**: Mengelola hubungan model yang kompleks dengan antarmuka yang intuitif.
-   **Authorization**: Terintegrasi dengan kebijakan model Laravel untuk keamanan yang konsisten.
-   **Soft Deletes**: Dukungan bawaan untuk soft-delete, pemulihan, dan penghapusan permanen.
-   **Multi-tenancy**: Dukungan untuk aplikasi multi-tenant dengan pemisahan data.
-   **Globalization**: Dukungan untuk multi-bahasa dan lokalisasi dengan penerjemahan yang mudah.
-   **Themes & Custom Styling**: Kemampuan untuk mengkustomisasi tampilan dengan tema dan CSS kustom.
-   **Health Checks**: Monitor kesehatan aplikasi dan dependensinya.
-   **Testing Utilities**: Alat bawaan untuk menguji komponen Filament.
-   **Optimizations**: Fitur caching untuk performa yang lebih baik di produksi.

Filament Resources memudahkan pembuatan antarmuka CRUD berdasarkan model Eloquent dengan fitur:

-   **Tipe Halaman**: Mendukung List, Create, Edit, View dan Simple (Modal) Resources.
-   **Filter Tabs**: Menambahkan tab di atas tabel untuk memfilter data berdasarkan kondisi tertentu.
-   **Record Title**: Mendefinisikan atribut yang digunakan untuk mengidentifikasi record.
-   **Custom Forms & Tables**: Antarmuka lengkap untuk membuat formulir dan tabel yang kompleks.
-   **Relation Managers**: Mengelola hubungan antar model dengan mudah.
-   **Authorization Integration**: Integrasi dengan kebijakan model Laravel.
-   **Customizable Navigation**: Menyesuaikan tampilan dan perilaku item navigasi.

Untuk dokumentasi lengkap tentang Filament 3, kunjungi [Dokumentasi Resmi Filament](https://filamentphp.com/docs).

### TailwindCSS

TailwindCSS adalah framework CSS utility-first yang kami gunakan untuk styling aplikasi:

-   **Pendekatan Utility-First**: Framework yang menyediakan class utilitas untuk membangun antarmuka dengan cepat langsung di HTML tanpa menulis CSS kustom.
-   **Zero-Runtime**: Bekerja dengan memindai file template untuk nama class, menghasilkan gaya yang sesuai dan menulis ke file CSS statis.
-   **Just-in-time Engine**: Kompilasi CSS yang sangat cepat dan hanya menghasilkan class yang benar-benar digunakan untuk file output minimal.
-   **Desain dengan Constraints**: Menyediakan sistem desain yang telah ditentukan sebelumnya yang membuat pembuatan UI yang konsisten secara visual menjadi lebih mudah.
-   **Responsive Design**: Desain responsif dengan prefiks breakpoint mudah (`sm:`, `md:`, `lg:`, `xl:`, `2xl:`) untuk mengadaptasi tata letak di berbagai ukuran layar.
-   **State Variants**: Styling mudah untuk berbagai state (`hover:`, `focus:`, `active:`, `disabled:`, `first:`, `last:`, dll).
-   **Dark Mode**: Dukungan dark mode bawaan dengan prefiks `dark:` yang memudahkan implementasi tema gelap.
-   **Customization**: Konfigurasi lengkap dengan file `tailwind.config.js` untuk tema, warna, spacing, dan lainnya sesuai kebutuhan proyek.
-   **Plugins Official**: Plugin resmi seperti Typography, Forms, Aspect Ratio, dan Container Queries untuk memperluas fungsionalitas.
-   **Layout Tools**: Utilitas layout yang kuat termasuk Flexbox, Grid, Positioning, dan Spacing untuk membuat layout kompleks.
-   **Typography**: Kontrol lengkap atas font family, size, weight, line-height, dan banyak lagi untuk tipografi profesional.
-   **Background & Borders**: Styling lengkap untuk background, gradients, borders, shadows, dan efek visual lainnya.
-   **Transitions & Animations**: Sistem transisi dan animasi yang dapat dikonfigurasi untuk interaksi dinamis.
-   **Transformations**: Kontrol untuk scale, rotate, translate, dan skew dengan nilai yang dapat disesuaikan.
-   **Interactivity**: Utilitas untuk cursor, user-select, scroll behavior, dan elemen interaktif lainnya.
-   **Accessibility**: Utilitas untuk memastikan konten dapat diakses oleh semua pengguna, termasuk screen readers.
-   **Performance Optimization**: Tools untuk mengoptimalkan produksi seperti purge CSS, minification, dan content hashing.

Untuk dokumentasi lengkap tentang TailwindCSS, kunjungi [Dokumentasi Resmi TailwindCSS](https://v3.tailwindcss.com/docs).

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
-   **Pelunasan**: Manajemen pelunasan pinjaman
-   **Deposito**: Pengelolaan deposito anggota
-   **Gadai**: Produk pinjaman dengan jaminan
-   **KreditElektronik**: Produk kredit untuk pembelian elektronik
-   **CicilanEmas**: Produk cicilan untuk pembelian emas
-   **TransaksiTabungan**: Rekaman transaksi tabungan
-   **TransaksiPinjaman**: Rekaman transaksi pinjaman
-   **TransaksiPelunasan**: Rekaman transaksi pelunasan
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

## Fitur Khusus

### QR Code Barcode Tabungan

Aplikasi ini dilengkapi dengan fitur cetak barcode QR Code untuk setiap rekening tabungan. Fitur ini memungkinkan:

-   **Cetak PDF Barcode**: Admin dapat mencetak barcode dalam format PDF untuk setiap rekening tabungan
-   **Public Scan Access**: QR Code dapat di-scan oleh siapa saja untuk melihat informasi dasar rekening
-   **Auto-Generated QR Code**: QR Code otomatis di-generate menggunakan QRServer API
-   **Temporary File Management**: QR Code disimpan sementara dan otomatis dihapus setelah PDF generated

**Cara Penggunaan:**

1. Login ke admin panel Filament
2. Buka halaman Rekening Tabungan
3. Klik action "Cetak Barcode" pada rekening yang diinginkan
4. PDF barcode akan otomatis ter-download
5. Scan QR Code untuk melihat detail rekening secara public

**Dokumentasi Lengkap:** Lihat [BARCODE_TABUNGAN.md](BARCODE_TABUNGAN.md) untuk detail implementasi dan troubleshooting.

## Lisensi

Aplikasi ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
