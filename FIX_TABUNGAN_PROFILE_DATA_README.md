# Script Perbaikan Data Tabungan Profile

## Deskripsi
Script ini digunakan untuk memperbaiki data `id_profile` di tabel `tabungans` agar sesuai dengan `id_user` yang valid di tabel `profiles`.

## Masalah yang Diperbaiki
- Data `id_profile` di tabel `tabungans` tidak cocok dengan `id_user` di tabel `profiles`
- Hal ini menyebabkan query di halaman `TabunganSaya.php` tidak mengembalikan data yang benar

## Cara Penggunaan

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

## Mekanisme Perbaikan

1. **Identifikasi Data Tidak Valid**: Script mencari `id_profile` di tabel `tabungans` yang tidak ada di `profiles.id_user`
2. **Fallback ID User**: Menggunakan `id_user` terkecil yang valid sebagai pengganti
3. **Update Data**: Mengupdate semua tabungan dengan `id_profile` tidak valid
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