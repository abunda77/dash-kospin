# Panduan Perintah CLI NPM yang Sering Digunakan

NPM (Node Package Manager) adalah manajer paket utama untuk ekosistem Node.js. Berikut adalah daftar perintah npm yang sering digunakan dalam pembuatan aplikasi berbasis Node.js:

## Dasar-dasar

-   `npm --version`
    Melihat versi npm yang terpasang.
-   `npm init`
    Membuat file package.json secara interaktif.
-   `npm init -y`
    Membuat file package.json dengan default.

## Instalasi Paket

-   `npm install <nama_paket>`
    Menginstal paket ke dependencies.
-   `npm install <nama_paket> --save-dev`
    Menginstal paket ke devDependencies.
-   `npm install`
    Menginstal semua dependencies yang ada di package.json.
-   `npm install -g <nama_paket>`
    Menginstal paket secara global.

## Menghapus & Update Paket

-   `npm uninstall <nama_paket>`
    Menghapus paket dari dependencies.
-   `npm update <nama_paket>`
    Memperbarui paket ke versi terbaru sesuai range di package.json.
-   `npm outdated`
    Melihat daftar paket yang sudah usang.

## Menjalankan Script

-   `npm run <nama_script>`
    Menjalankan script yang didefinisikan di package.json.
-   `npm start`
    Shortcut untuk `npm run start`.
-   `npm test`
    Shortcut untuk `npm run test`.

## Audit & Maintenance

-   `npm audit`
    Mengecek kerentanan keamanan pada dependencies.
-   `npm audit fix`
    Memperbaiki kerentanan secara otomatis jika memungkinkan.
-   `npm cache clean --force`
    Membersihkan cache npm.

## Info & Lain-lain

-   `npm list`
    Melihat daftar paket yang terinstal di project.
-   `npm list -g --depth=0`
    Melihat daftar paket global.
-   `npm help`
    Melihat bantuan perintah npm.

---

Panduan ini hanya mencakup perintah npm yang paling sering digunakan. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi NPM](https://docs.npmjs.com/).
