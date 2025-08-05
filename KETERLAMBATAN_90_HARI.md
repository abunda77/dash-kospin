# Dokumentasi List Keterlambatan 90 Hari

## Deskripsi
Halaman **List Keterlambatan 90 Hari** menampilkan informasi lengkap tentang akun pinjaman yang mengalami keterlambatan lebih dari 90 hari. Halaman ini dirancang khusus untuk monitoring keterlambatan kritis yang memerlukan tindakan penagihan intensif.

## Fitur Utama

### 1. Dashboard Statistics Widget
Widget statistik yang menampilkan:
- **Total Akun Bermasalah**: Jumlah akun dengan keterlambatan > 90 hari
- **Total Nominal Pinjaman**: Nilai total pinjaman yang bermasalah
- **Total Tunggakan**: Total pokok + bunga + denda yang belum dibayar
- **Total Denda**: Akumulasi denda keterlambatan
- **Rata-rata Keterlambatan**: Rata-rata hari keterlambatan
- **Total Angsuran Pokok**: Total angsuran pokok yang terhutang

### 2. Tabel Data Keterlambatan
Menampilkan detail untuk setiap akun:
- Nomor urut
- Nama nasabah
- Nomor pinjaman
- Nominal pinjaman
- Angsuran pokok per bulan
- Bunga per bulan
- Denda akumulasi
- Total tunggakan (pokok + bunga + denda)
- Tanggal pinjaman
- Jumlah hari keterlambatan dengan color coding:
  - **Kuning**: 90-119 hari (Medium Risk)
  - **Orange**: 120-179 hari (High Risk)  
  - **Merah**: 180+ hari (Critical Risk)
- Link WhatsApp untuk kontak langsung

### 3. Fitur Peringatan Urgent
- Tombol "Kirim Peringatan Urgent" dengan pesan khusus untuk keterlambatan kritis
- Pesan otomatis berisi:
  - Detail tunggakan lengkap
  - Jumlah hari keterlambatan
  - Peringatan konsekuensi hukum
  - Ajakan untuk segera menghubungi kantor

### 4. Laporan PDF
- Generate laporan PDF dengan format landscape
- Include statistik ringkasan
- Risk level indicator untuk setiap akun
- Rekomendasi tindakan berdasarkan tingkat risiko

## Perhitungan Financial

### Formula Denda Harian
```
Denda per Hari = (5% × Angsuran Total) ÷ 30 hari
Angsuran Total = Angsuran Pokok + Bunga per Bulan
```

### Formula Total Tunggakan
```
Jumlah Bulan Terlambat = CEILING(Hari Terlambat ÷ 30)
Total Pokok = Angsuran Pokok × Jumlah Bulan Terlambat
Total Bunga = Bunga per Bulan × Jumlah Bulan Terlambat
Total Denda = Denda per Hari × Hari Terlambat
Total Tunggakan = Total Pokok + Total Bunga + Total Denda
```

## Kriteria Keterlambatan 90+ Hari

### Kondisi Pinjaman yang Ditampilkan:
1. **Status pinjaman = 'approved'**
2. **Belum ada pembayaran di bulan ini**
3. **Salah satu dari kondisi berikut:**
   - Memiliki transaksi pembayaran dengan jatuh tempo > 90 hari dari hari ini
   - Belum pernah ada transaksi pembayaran dan sudah > 90 hari dari tanggal pinjaman + 1 bulan

## Risk Level Classification

### Medium Risk (90-119 hari)
- Color: **Kuning**
- Tindakan: Monitoring intensif, pengingat harian
- Follow-up: Jadwalkan pertemuan dengan nasabah

### High Risk (120-179 hari)
- Color: **Orange** 
- Tindakan: Kontak langsung, review agunan
- Follow-up: Koordinasi dengan tim collection

### Critical Risk (180+ hari)
- Color: **Merah**
- Tindakan: Kontak langsung + kunjungan, restrukturisasi
- Follow-up: Pertimbangkan tindakan hukum

## Integrasi dengan Sistem

### WhatsApp Integration
- Format nomor otomatis (hapus karakter non-digit, tambah 62 untuk Indonesia)
- Pesan template dengan variabel dinamis
- Tracking status pengiriman via webhook N8N

### Permission System
- Permission: `page_ListKeterlambatan90Hari`
- Guard: `admin`
- Auto-assigned ke role `super_admin` dan `admin`

### Navigation
- Group: **Pinjaman**
- Badge: Menampilkan jumlah akun bermasalah secara real-time
- Badge Color: **Danger** (merah)

## Widget Dashboard
Widget `CriticalOverdueWidget` di dashboard utama menampilkan:
- Ringkasan keterlambatan kritis
- Persentase risiko dari total pinjaman aktif
- Link langsung ke halaman detail

## Files yang Terlibat

### Core Files:
- `app/Filament/Pages/ListKeterlambatan90Hari.php` - Main page class
- `resources/views/filament/pages/list-keterlambatan-90-hari.blade.php` - Page view
- `resources/views/pdf/keterlambatan-90-hari.blade.php` - PDF template

### Widget:
- `app/Filament/Widgets/CriticalOverdueWidget.php` - Dashboard widget

### Permission:
- `app/Console/Commands/AssignListKeterlambatan90HariPermission.php` - Permission setup

## Cara Penggunaan

1. **Akses Halaman**: Navigasi → Pinjaman → List Telat > 90 Hari
2. **Lihat Statistics**: Review widget statistik di bagian atas
3. **Analisis Data**: Gunakan tabel untuk melihat detail setiap akun
4. **Ambil Tindakan**: 
   - Klik link WhatsApp untuk kontak langsung
   - Gunakan tombol "Kirim Peringatan Urgent" untuk pesan otomatis
5. **Generate Laporan**: Klik "Cetak Laporan" untuk export PDF

## Tips Monitoring

- Check badge notification di navigation untuk update real-time
- Gunakan color coding hari terlambat untuk prioritas tindakan
- Monitor dashboard widget untuk trend keterlambatan kritis
- Review laporan PDF secara berkala untuk analisis manajemen

## Troubleshooting

### Jika halaman tidak muncul:
```bash
php artisan shield:generate --all
php artisan permission:assign-keterlambatan-90-hari
```

### Jika widget tidak muncul di dashboard:
- Pastikan user memiliki permission `page_ListKeterlambatan90Hari`
- Clear cache: `php artisan config:clear`

### Jika PDF tidak generate:
- Pastikan DomPDF ter-install
- Check storage permissions untuk folder temp
