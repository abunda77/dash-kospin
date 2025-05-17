# Panduan Perintah Artisan Laravel yang Sering Digunakan

Artisan adalah command line interface (CLI) bawaan Laravel yang sangat membantu dalam pengembangan aplikasi. Berikut adalah daftar perintah artisan yang paling sering digunakan:

## Dasar-dasar

-   `php artisan --version`
    Melihat versi Laravel yang digunakan.
-   `php artisan list`
    Melihat daftar semua perintah artisan.
-   `php artisan help <perintah>`
    Melihat bantuan detail untuk perintah tertentu.

## Serve & Environment

-   `php artisan serve`
    Menjalankan server pengembangan lokal.
-   `php artisan env`
    Menampilkan environment aplikasi saat ini.

## Cache

-   `php artisan cache:clear`
    Menghapus cache aplikasi.
-   `php artisan config:cache`
    Membuat cache konfigurasi.
-   `php artisan route:cache`
    Membuat cache route.
-   `php artisan view:clear`
    Menghapus cache view.

## Database & Migration

-   `php artisan migrate`
    Menjalankan semua migrasi database.
-   `php artisan migrate:rollback`
    Mengembalikan migrasi terakhir.
-   `php artisan migrate:refresh`
    Mengulang migrasi (rollback lalu migrate ulang).
-   `php artisan db:seed`
    Menjalankan seeder database.
-   `php artisan migrate:fresh --seed`
    Menghapus semua tabel, migrasi ulang, dan menjalankan seeder.

## Model, Controller, dan Resource

-   `php artisan make:model NamaModel`
    Membuat model baru.
-   `php artisan make:controller NamaController`
    Membuat controller baru.
-   `php artisan make:migration buat_nama_tabel`
    Membuat file migrasi baru.
-   `php artisan make:seeder NamaSeeder`
    Membuat seeder baru.
-   `php artisan make:factory NamaFactory`
    Membuat factory baru.
-   `php artisan make:resource NamaResource`
    Membuat resource baru.

## Queue & Scheduler

-   `php artisan queue:work`
    Menjalankan worker queue.
-   `php artisan schedule:run`
    Menjalankan tugas terjadwal (scheduler).

## Lain-lain

-   `php artisan tinker`
    Masuk ke REPL Laravel untuk eksekusi kode secara interaktif.
-   `php artisan route:list`
    Melihat daftar semua route yang terdaftar.
-   `php artisan config:clear`
    Menghapus cache konfigurasi.
-   `php artisan key:generate`
    Membuat application key baru.

---

Panduan ini hanya mencakup perintah artisan yang paling sering digunakan. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi Laravel](https://laravel.com/docs/artisan).
