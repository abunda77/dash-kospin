# Panduan Perintah CLI MySQL/MariaDB yang Sering Digunakan

MySQL/MariaDB CLI digunakan untuk mengelola database langsung dari terminal. Berikut adalah perintah yang sering digunakan:

## Koneksi & Dasar

-   `mysql -u <user> -p`
    Masuk ke MySQL/MariaDB dengan user tertentu.
-   `mysql -u <user> -p -h <host>`
    Koneksi ke server database di host tertentu.
-   `exit`
    Keluar dari CLI MySQL/MariaDB.

## Database

-   `SHOW DATABASES;`
    Melihat daftar database.
-   `CREATE DATABASE nama_db;`
    Membuat database baru.
-   `DROP DATABASE nama_db;`
    Menghapus database.
-   `USE nama_db;`
    Memilih database yang akan digunakan.

## Tabel

-   `SHOW TABLES;`
    Melihat daftar tabel di database aktif.
-   `DESCRIBE nama_tabel;`
    Melihat struktur tabel.
-   `CREATE TABLE ...`
    Membuat tabel baru.
-   `DROP TABLE nama_tabel;`
    Menghapus tabel.

## Query Data

-   `SELECT * FROM nama_tabel;`
    Menampilkan semua data dari tabel.
-   `INSERT INTO nama_tabel ...`
    Menambah data ke tabel.
-   `UPDATE nama_tabel SET ...`
    Mengubah data di tabel.
-   `DELETE FROM nama_tabel WHERE ...`
    Menghapus data dari tabel.

## Import & Export

-   `mysqldump -u <user> -p nama_db > backup.sql`
    Export database ke file SQL.
-   `mysql -u <user> -p nama_db < backup.sql`
    Import database dari file SQL.

---

Panduan ini hanya mencakup perintah dasar dan penting. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi MySQL](https://dev.mysql.com/doc/) atau [dokumentasi MariaDB](https://mariadb.com/kb/en/mariadb-documentation/).
