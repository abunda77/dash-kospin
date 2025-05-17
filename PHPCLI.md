# Panduan Perintah CLI PHP yang Sering Digunakan

PHP CLI (Command Line Interface) memungkinkan Anda menjalankan skrip PHP langsung dari terminal. Berikut adalah perintah yang sering digunakan:

## Dasar-dasar

-   `php -v`
    Melihat versi PHP yang terpasang.
-   `php -m`
    Melihat daftar ekstensi PHP yang terpasang.
-   `php -i`
    Melihat informasi konfigurasi PHP (phpinfo).
-   `php -r 'kode_php;'`
    Menjalankan kode PHP satu baris langsung dari terminal.

## Menjalankan File PHP

-   `php file.php`
    Menjalankan file PHP di terminal.
-   `php -S localhost:8000`
    Menjalankan built-in web server PHP di port 8000.

## Composer & Dependency

-   `php composer.phar <perintah>`
    Menjalankan Composer secara lokal jika tidak diinstal global.

## Lain-lain

-   `php --ini`
    Melihat lokasi file konfigurasi php.ini.
-   `php --help`
    Melihat bantuan perintah PHP CLI.

---

Panduan ini hanya mencakup perintah dasar dan penting. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi PHP](https://www.php.net/manual/en/features.commandline.php).
