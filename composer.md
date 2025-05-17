# Panduan Perintah CLI Composer yang Sering Digunakan

Composer adalah dependency manager utama untuk PHP. Berikut adalah daftar perintah Composer yang sering digunakan:

## Dasar-dasar

-   `composer --version`
    Melihat versi Composer yang terpasang.
-   `composer init`
    Membuat file composer.json secara interaktif.
-   `composer install`
    Menginstal semua dependency dari composer.json.
-   `composer update`
    Memperbarui semua dependency ke versi terbaru sesuai constraint.

## Dependency

-   `composer require <package>`
    Menambah/menginstal package ke project.
-   `composer remove <package>`
    Menghapus package dari project.
-   `composer show`
    Melihat daftar package yang terinstal.

## Autoload

-   `composer dump-autoload`
    Membuat ulang file autoload.

## Lain-lain

-   `composer create-project <package> <folder>`
    Membuat project baru dari package (misal: Laravel).
-   `composer update <package>`
    Memperbarui satu package tertentu.
-   `composer help`
    Melihat bantuan perintah Composer.

---

Panduan ini hanya mencakup perintah dasar dan penting. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi Composer](https://getcomposer.org/doc/).
