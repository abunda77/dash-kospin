# Perintah-Perintah Umum Composer dan Kegunaannya

Composer adalah alat manajemen dependensi untuk PHP. Berikut adalah ringkasan perintah-perintah umum:

1.  **`composer init`**

    -   **Kegunaan:** Membuat file `composer.json` secara interaktif untuk proyek baru.

2.  **`composer install`** atau `composer i`

    -   **Kegunaan:** Menginstal semua dependensi dari `composer.json`. Jika `composer.lock` ada, versi di lock file yang akan diinstal. Jika tidak, akan dibuat `composer.lock` baru setelah dependensi di-resolve.
    -   **Opsi umum:** `--no-dev` (produksi), `-o` (optimasi autoloader).

3.  **`composer update`** atau `composer u` / `upgrade`

    -   **Kegunaan:** Memperbarui dependensi ke versi terbaru sesuai batasan di `composer.json` dan memperbarui `composer.lock`. Bisa untuk semua atau paket spesifik.
    -   **Opsi umum:** Sama seperti `install`.

4.  **`composer require`** atau `composer r`

    -   **Kegunaan:** Menambahkan paket baru ke `composer.json` dan menginstalnya.
    -   **Contoh:** `composer require vendor/package:^1.0`
    -   **Opsi umum:** `--dev` (untuk pengembangan), `--no-update`.

5.  **`composer remove`** atau `composer rm`

    -   **Kegunaan:** Menghapus paket dari `composer.json` dan direktori `vendor`.
    -   **Opsi umum:** `--dev`.

6.  **`composer show`** atau `composer info`

    -   **Kegunaan:** Menampilkan informasi tentang paket yang terinstal atau tersedia.
    -   **Opsi umum:** `--latest`, `--outdated`, `-t` (tree).

7.  **`composer dump-autoload`** atau `composer dumpautoload`

    -   **Kegunaan:** Memperbarui file `vendor/autoload.php` tanpa menjalankan `install`/`update`.
    -   **Opsi umum:** `-o` (optimasi).

8.  **`composer search`**

    -   **Kegunaan:** Mencari paket di repositori (misalnya Packagist).

9.  **`composer validate`**

    -   **Kegunaan:** Memvalidasi format `composer.json` dan sinkronisasi dengan `composer.lock`.

10. **`composer self-update`** atau `composer selfupdate`

    -   **Kegunaan:** Memperbarui Composer ke versi terbaru.

11. **`composer create-project`**

    -   **Kegunaan:** Membuat proyek baru dari paket yang sudah ada.
    -   **Contoh:** `composer create-project laravel/laravel nama-proyek`

12. **`composer global`**
    -   **Kegunaan:** Menjalankan perintah Composer untuk paket yang diinstal secara global (untuk alat CLI).

## Fungsi Utama Composer:

-   **Manajemen Dependensi:** Mengelola pustaka eksternal.
-   **Instalasi Otomatis:** Mengunduh dan mengatur dependensi.
-   **Autoloading:** Mempermudah pemanggilan kelas tanpa `require` manual.
-   **Pembaruan Dependensi:** Mengelola update pustaka.
-   **Manajemen Versi:** Memastikan konsistensi versi dependensi.
