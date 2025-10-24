# Data Karyawan - Simplified Version

## Perubahan yang Dilakukan

Form data karyawan telah disederhanakan sesuai dengan contoh data yang diberikan. Struktur baru hanya mencakup field-field esensial.

## Struktur Database

Tabel `karyawans` sekarang memiliki kolom:

1. `id` - Primary key
2. `nik_karyawan` - Nomor Induk Karyawan (unique)
3. `nama` - Nama lengkap karyawan
4. `alamat` - Alamat lengkap
5. `tempat_lahir` - Tempat lahir
6. `tanggal_lahir` - Tanggal lahir
7. `no_telepon` - Nomor telepon karyawan
8. `no_telepon_keluarga` - Nomor HP keluarga (optional)
9. `foto_profil` - Foto profile (JSON, optional)
10. `is_active` - Status aktif (boolean, default: true)
11. `created_at` - Timestamp dibuat
12. `updated_at` - Timestamp diperbarui

## Form Input

Form input di Filament Admin sekarang hanya memiliki 9 field:

1. **No NIK** - Text input (required, unique)
2. **Nama** - Text input (required)
3. **Alamat** - Textarea (required, 3 rows)
4. **Tempat Lahir** - Text input (required)
5. **Tanggal Lahir** - Date picker (required, format: dd/mm/yyyy)
6. **No. Telepon** - Phone input dengan country code ID (required)
7. **No. HP Keluarga** - Phone input dengan country code ID (optional)
8. **Foto Profile** - File upload dengan image editor (optional)
9. **Status Aktif** - Toggle (default: true)

## Contoh Data

```
No NIK: 16011
Nama: Hadianto Sutisna
Alamat: Cinisti Kaler Bayongbong Garut Jawa Barat
Tempat Lahir: Garut
Tanggal Lahir: 13/03/2000
No. Telepon: 081542778675
No. HP Keluarga: 081947534822
Foto Profile: [upload image]
Status Aktif: ✓
```

## Tabel View

Kolom yang ditampilkan di tabel:

-   No NIK (searchable, sortable)
-   Foto (circular avatar)
-   Nama (searchable, sortable)
-   Alamat (searchable, limited 50 chars)
-   Tempat Lahir (hidden by default)
-   Tanggal Lahir (hidden by default)
-   No. Telepon (searchable)
-   No. HP Keluarga (hidden by default)
-   Status (icon: check/x)
-   Dibuat (hidden by default)
-   Diperbarui (hidden by default)

## Filter

-   Status Aktif (Semua / Aktif / Tidak Aktif)

## Actions

-   View - Melihat detail karyawan
-   Edit - Mengedit data karyawan
-   Delete - Menghapus data karyawan

## Info View

Detail karyawan ditampilkan dalam section "Informasi Karyawan" dengan layout 2 kolom yang menampilkan semua field.

## File yang Dimodifikasi

1. `database/migrations/2025_01_01_094345_create_karyawans_table.php`
2. `app/Models/Karyawan.php`
3. `app/Filament/Resources/KaryawanResource.php`

## Cara Menggunakan

1. Akses admin panel: `/admin/karyawans`
2. Klik "New" untuk menambah karyawan baru
3. Isi form dengan data yang diperlukan
4. Klik "Create" untuk menyimpan

## Migration

Untuk menerapkan perubahan struktur database:

```bash
php artisan migrate:fresh --seed
```

**Catatan:** Perintah di atas akan menghapus semua data yang ada. Jika ingin mempertahankan data, gunakan migration rollback dan up secara manual.
