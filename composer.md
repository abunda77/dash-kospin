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

````markdown
# Rangkuman Audit Keamanan Composer & Perbaikan Dependency

## 1. Kondisi Awal

Hasil awal `composer audit` menunjukkan terdapat banyak vulnerability pada dependency project Laravel, yaitu:

- 23 security advisories
- 14 package terdampak
- Beberapa severity penting:
  - `critical`
  - `high`
  - `medium`
  - `low`

Package yang terdampak antara lain:

- `livewire/livewire`
- `league/commonmark`
- `phpunit/phpunit`
- `psy/psysh`
- beberapa package Symfony:
  - `symfony/mime`
  - `symfony/mailer`
  - `symfony/http-foundation`
  - `symfony/http-kernel`
  - `symfony/routing`
  - `symfony/html-sanitizer`
  - `symfony/yaml`
  - `symfony/process`
  - `symfony/dom-crawler`

Yang paling berbahaya adalah:

```text
Package  : livewire/livewire
Severity : critical
CVE      : CVE-2025-54068
Issue    : Remote Command Execution during component property update hydration
Affected : >=3.0.0-beta.1,<3.6.4
````

Artinya, semua versi Livewire 3 sebelum `3.6.4` masuk kategori rentan.

---

## 2. Analisis Awal

Perintah `composer update` saja belum tentu cukup, karena:

* `composer update` akan memperbarui dependency sesuai batas versi di `composer.json`
* package tertentu bisa saja menahan versi package lain
* jika ada dependency yang mengunci versi vulnerable, Composer tidak bisa menaikkan package tersebut
* hasil akhirnya harus tetap dicek dengan `composer audit`

Perintah yang disarankan saat itu:

```bash
composer update livewire/livewire --with-all-dependencies
composer audit
```

Jika masih gagal, cek penyebabnya dengan:

```bash
composer why-not livewire/livewire 3.6.4
```

---

## 3. Setelah Update Pertama

Setelah update, hasil audit membaik drastis.

Dari awalnya banyak vulnerability, tersisa hanya satu:

```text
Found 1 security vulnerability advisory affecting 1 package:

Package  : livewire/livewire
Severity : critical
CVE      : CVE-2025-54068
Affected : >=3.0.0-beta.1,<3.6.4
```

Ini menunjukkan sebagian besar vulnerability sudah berhasil diperbaiki, tetapi masalah paling kritis masih tersisa.

---

## 4. Versi Livewire Saat Itu

Hasil:

```bash
composer show livewire/livewire
```

menunjukkan:

```text
livewire/livewire v3.5.12
```

Masalahnya:

```text
v3.5.12 < v3.6.4
```

Jadi Livewire masih berada dalam rentang vulnerable.

Target aman:

```text
livewire/livewire >= 3.6.4
```

---

## 5. Penyebab Livewire Tidak Bisa Naik Versi

Perintah berikut dijalankan:

```bash
composer why-not livewire/livewire 3.6.4
```

Hasilnya:

```text
filament/support v3.2.133 requires livewire/livewire (3.5.12)
```

Kesimpulan:

* Masalah bukan di Composer
* Masalah bukan langsung di Livewire
* Penyebabnya adalah `filament/support v3.2.133`
* Filament versi tersebut mengunci Livewire tepat ke `3.5.12`

Dengan kata lain, Livewire tidak bisa naik ke versi aman karena Filament menahannya.

---

## 6. Solusi yang Dilakukan

Solusi yang disarankan adalah memperbarui Filament beserta Livewire secara bersamaan:

```bash
composer update filament/* livewire/livewire --with-all-dependencies
```

Atau versi pendek:

```bash
composer update filament/* livewire/livewire -W
```

Tujuannya:

* memperbarui package Filament yang mengunci Livewire
* membuka jalan agar Livewire bisa naik ke versi aman
* tetap berada di jalur Filament v3, bukan lompat besar ke Filament v4/v5

---

## 7. Hasil Update

Update berhasil dilakukan.

Package penting yang berubah:

```text
filament/actions        v3.2.133 => v3.3.52
filament/filament       v3.2.133 => v3.3.52
filament/forms          v3.2.133 => v3.3.52
filament/infolists      v3.2.133 => v3.3.52
filament/notifications  v3.2.133 => v3.3.52
filament/support        v3.2.133 => v3.3.52
filament/tables         v3.2.133 => v3.3.52
filament/widgets        v3.2.133 => v3.3.52

livewire/livewire       v3.5.12  => v3.8.1
```

Ini berarti Livewire sudah naik melewati batas aman `3.6.4`.

Hasil audit akhir:

```text
No security vulnerability advisories found.
```

Status:

```text
Composer audit bersih
Livewire aman
Filament berhasil diperbarui
```

---

## 8. Perbedaan `composer update` dan `composer install`

### `composer update`

Digunakan untuk memperbarui dependency berdasarkan aturan di `composer.json`.

Perintah ini:

```bash
composer update
```

atau:

```bash
composer update filament/* livewire/livewire -W
```

akan:

* membaca aturan versi dari `composer.json`
* mencari versi terbaru yang masih sesuai constraint
* mengubah `composer.lock`
* mengubah isi folder `vendor`
* biasanya tidak mengubah `composer.json`

Contoh dalam kasus ini:

```text
livewire/livewire v3.5.12 => v3.8.1
filament/filament v3.2.133 => v3.3.52
```

---

### `composer install`

Digunakan untuk menginstall dependency berdasarkan versi yang sudah dikunci di `composer.lock`.

Perintah ini:

```bash
composer install
```

akan:

* membaca `composer.lock`
* menginstall versi package yang persis tertulis di sana
* tidak mencari versi terbaru
* tidak mengubah `composer.json`
* ideal untuk production/deployment

Untuk production:

```bash
composer install --no-dev --optimize-autoloader
```

---

## 9. Kenapa `composer.json` Tidak Berubah?

Itu normal.

Dalam kasus ini, `composer.json` tidak berubah karena dependency yang lebih baru masih sesuai dengan constraint yang sudah ada.

Misalnya jika sebelumnya `composer.json` mengizinkan:

```json
"filament/filament": "^3.2"
```

maka Composer masih boleh menaikkan ke:

```text
v3.3.52
```

Karena masih berada dalam major version 3.

Jadi yang berubah adalah:

```text
composer.lock
vendor/
public/js dan public/css jika asset Filament dipublish ulang
```

Bukan necesariamente `composer.json`.

---

## 10. File yang Perlu Diperhatikan untuk Commit

Minimal yang perlu dicek:

```bash
git status
```

Kemungkinan file yang berubah:

```text
composer.lock
public/js/...
public/css/...
```

Karena saat update, perintah berikut berjalan otomatis:

```bash
php artisan filament:upgrade
```

Dan Filament mem-publish ulang asset ke folder `public`.

Jika asset publik memang disimpan dalam repository, maka commit juga:

```bash
git add composer.lock public/js public/css
git commit -m "Update Filament and Livewire security patches"
```

Jika `composer.json` tidak berubah, tidak perlu dipaksa ikut commit.

---

## 11. Langkah Validasi Setelah Update

Setelah update dependency dan audit bersih, tetap perlu validasi aplikasi.

Jalankan:

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Jika ada test:

```bash
php artisan test
```

Jika memakai build frontend:

```bash
npm run build
```

Cek manual bagian yang memakai Filament dan Livewire:

```text
- Login admin Filament
- Dashboard
- Form create/edit
- Table list
- Search
- Filter
- Pagination
- Upload file
- Date picker
- Rich editor
- Markdown editor
- Modal action
- Bulk action
- Notification
- Export/import jika ada
```

---

## 12. Alur Deployment yang Benar

### Di local/staging

```bash
composer update filament/* livewire/livewire -W
composer audit
php artisan test
```

Commit hasil update:

```bash
git add composer.lock public/js public/css
git commit -m "Update Filament and Livewire security patches"
```

### Di production

Jangan jalankan:

```bash
composer update
```

Gunakan:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize
```

Alasannya:

* production harus memakai versi yang sudah dites
* versi tersebut dikunci di `composer.lock`
* `composer update` di production bisa mengambil versi baru yang belum diuji

---

## 13. Kesimpulan Akhir

Masalah utama awalnya adalah vulnerability critical di Livewire:

```text
CVE-2025-54068
livewire/livewire < 3.6.4
```

Livewire tidak bisa langsung naik karena dikunci oleh:

```text
filament/support v3.2.133
```

Solusi yang berhasil:

```bash
composer update filament/* livewire/livewire --with-all-dependencies
```

Hasil akhirnya:

```text
filament/*        v3.2.133 => v3.3.52
livewire/livewire v3.5.12  => v3.8.1
composer audit    No security vulnerability advisories found
```

Status akhir:

```text
AMAN dari sisi Composer audit
Perlu tetap dilakukan regression test aplikasi
composer.lock wajib di-commit
composer update jangan dijalankan langsung di production
```

```
```
