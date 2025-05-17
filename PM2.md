# Panduan Perintah CLI PM2 yang Sering Digunakan

PM2 adalah process manager untuk aplikasi Node.js yang berjalan di production. Berikut adalah perintah PM2 yang sering digunakan:

## Dasar-dasar

-   `pm2 --version`
    Melihat versi PM2 yang terpasang.
-   `pm2 start <file.js>`
    Menjalankan aplikasi Node.js dengan PM2.
-   `pm2 list`
    Melihat daftar aplikasi yang dikelola PM2.
-   `pm2 stop <nama|id>`
    Menghentikan aplikasi tertentu.
-   `pm2 restart <nama|id>`
    Merestart aplikasi tertentu.
-   `pm2 delete <nama|id>`
    Menghapus aplikasi dari PM2.

## Monitoring & Log

-   `pm2 monit`
    Melihat monitoring aplikasi secara real-time.
-   `pm2 logs`
    Melihat log semua aplikasi.
-   `pm2 logs <nama|id>`
    Melihat log aplikasi tertentu.

## Konfigurasi & Lain-lain

-   `pm2 save`
    Menyimpan konfigurasi proses agar otomatis dijalankan ulang setelah reboot.
-   `pm2 startup`
    Membuat PM2 berjalan otomatis saat booting.
-   `pm2 reload all`
    Reload semua aplikasi tanpa downtime.
-   `pm2 kill`
    Menghentikan semua proses PM2.

---

Panduan ini hanya mencakup perintah dasar dan penting. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi PM2](https://pm2.keymetrics.io/docs/usage/quick-start/).
