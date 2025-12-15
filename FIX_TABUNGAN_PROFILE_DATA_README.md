# Script Perbaikan Data Tabungan Profile

## Deskripsi
Script ini digunakan untuk memperbaiki data `id_profile` di tabel `tabungans` agar sesuai dengan `id_user` yang valid di tabel `profiles`.

## Masalah yang Diperbaiki
- Data `id_profile` di tabel `tabungans` tidak cocok dengan `id_user` di tabel `profiles`
- Hal ini menyebabkan query di halaman `TabunganSaya.php` tidak mengembalikan data yang benar

## Cara Penggunaan

### Opsi Command
- `--dry-run`: Jalankan pengecekan tanpa melakukan perubahan
- `--force`: Jalankan perbaikan tanpa konfirmasi interaktif

### 1. Pengecekan Data (Dry Run)
Jalankan command dengan opsi `--dry-run` untuk melihat apa yang akan diperbaiki tanpa melakukan perubahan:

```bash
php artisan app:fix-tabungan-profile-data --dry-run
```

### 2. Perbaikan Data
Jalankan command tanpa opsi untuk melakukan perbaikan sebenarnya:

```bash
php artisan app:fix-tabungan-profile-data
```

### 3. Perbaikan Data Tanpa Konfirmasi (untuk automation/scripting)
Gunakan opsi `--force` untuk melewati konfirmasi interaktif:

```bash
php artisan app:fix-tabungan-profile-data --force
```

## Contoh Output

### Dry Run Mode:
```
Memeriksa data tabungan profile...
ID Profile di tabel tabungans: 1, 2, 41
ID User valid di tabel profiles: 33, 34, 68, 69, 70
⚠️  Ditemukan id_profile yang tidak valid: 1, 2, 41
ID User fallback yang akan digunakan: 33
🔍 Dry run mode - hanya menampilkan apa yang akan diperbaiki:
  - 4 tabungan dengan id_profile 1 akan diubah menjadi 33
  - 1 tabungan dengan id_profile 2 akan diubah menjadi 33
  - 1 tabungan dengan id_profile 41 akan diubah menjadi 33
Gunakan tanpa --dry-run untuk melakukan perbaikan sebenarnya.
```

### Mode Perbaikan:
```
Memeriksa data tabungan profile...
ID Profile di tabel tabungans: 1, 2, 41
ID User valid di tabel profiles: 33, 34, 68, 69, 70
⚠️  Ditemukan id_profile yang tidak valid: 1, 2, 41
ID User fallback yang akan digunakan: 33
Apakah Anda yakin ingin memperbaiki data ini? (yes/no) [yes]:
> yes

🔧 Memulai perbaikan data...
  ✓ 4 tabungan dengan id_profile 1 diperbaiki
  ✓ 1 tabungan dengan id_profile 2 diperbaiki
  ✓ 1 tabungan dengan id_profile 41 diperbaiki
✅ Perbaikan selesai. Total 6 data tabungan diperbaiki.
✅ Semua data id_profile sekarang sudah valid.
```

### Mode Force (--force):
```
Memeriksa data tabungan profile...
ID Profile di tabel tabungans: 1, 2, 33, 999
ID User valid di tabel profiles: 33, 34, 68, 69, 70
⚠️  Ditemukan id_profile yang tidak valid: 1, 2, 999
Mapping perbaikan:
  1 → 33  (profiles.id=1 → id_user=33)
  2 → 34  (profiles.id=2 → id_user=34)
  999 → 33 (tidak ada profiles.id=999, fallback ke 33)
🔧 Memulai perbaikan data...
  ✓ 1 tabungan dengan id_profile 1 diperbaiki menjadi 33
  ✓ 1 tabungan dengan id_profile 2 diperbaiki menjadi 34
  ✓ 1 tabungan dengan id_profile 999 diperbaiki menjadi 33
✅ Perbaikan selesai. Total 3 data tabungan diperbaiki.
✅ Semua data id_profile sekarang sudah valid.
```

## Mekanisme Perbaikan

1. **Identifikasi Data Tidak Valid**: Script mencari `id_profile` di tabel `tabungans` yang tidak ada di `profiles.id_user`

2. **Mapping Berdasarkan profiles.id**:
   - Script mengasumsikan bahwa `id_profile` yang salah mereferensi `profiles.id` (bukan `profiles.id_user`)
   - Untuk setiap `id_profile` yang tidak valid, script mencari profile dengan `id = id_profile`
   - Jika ditemukan, ambil `id_user` dari profile tersebut sebagai nilai yang benar
   - Jika tidak ditemukan, gunakan fallback ke `id_user` terkecil yang valid

   **Contoh Mapping**:
   - Jika `profiles.id = 1` memiliki `id_user = 33`, maka `id_profile = 1` → `33`
   - Jika `profiles.id = 2` memiliki `id_user = 34`, maka `id_profile = 2` → `34`

3. **Update Data**: Mengupdate semua tabungan dengan `id_profile` tidak valid berdasarkan mapping

4. **Verifikasi**: Memastikan tidak ada lagi data yang tidak valid

## Catatan Penting

- **Backup Database**: Selalu lakukan backup database sebelum menjalankan script perbaikan
- **Testing**: Gunakan `--dry-run` terlebih dahulu untuk memastikan perbaikan yang akan dilakukan
- **Konfirmasi**: Script akan meminta konfirmasi sebelum melakukan perubahan sebenarnya
- **Fallback Strategy**: Script menggunakan ID user pertama yang valid sebagai fallback. Jika diperlukan mapping yang lebih spesifik, script dapat dimodifikasi.

## Troubleshooting

### Jika masih ada data tidak valid setelah perbaikan:
1. Periksa apakah ada profile baru yang ditambahkan setelah script dijalankan
2. Jalankan script lagi untuk memperbaiki data baru

### Jika script tidak menemukan data untuk diperbaiki:
- Data sudah dalam kondisi baik
- Tidak ada masalah dengan relasi profile

## File Terkait

- `app/Console/Commands/FixTabunganProfileData.php` - Script utama
- `app/Models/Tabungan.php` - Model Tabungan (pastikan relasi profile sudah benar)
- `app/Models/Profile.php` - Model Profile