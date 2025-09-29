# Laporan Nasabah Aktif

## Deskripsi
Halaman ini menampilkan laporan nasabah yang melakukan transaksi dalam periode tertentu (default 90 hari terakhir). Laporan mencakup transaksi tabungan dan pinjaman.

## Fitur

### 1. Filter Laporan
- **Tanggal Mulai**: Tanggal awal periode laporan
- **Tanggal Selesai**: Tanggal akhir periode laporan
- **Jenis Transaksi**:
  - Semua Transaksi (Tabungan & Pinjaman)
  - Transaksi Tabungan saja
  - Transaksi Pinjaman saja

### 2. Statistik Dashboard
- **Total Nasabah Aktif**: Jumlah nasabah dengan transaksi dalam periode
- **Transaksi Tabungan**: Total transaksi tabungan dalam periode
- **Transaksi Pinjaman**: Total transaksi pinjaman dalam periode

### 3. Tabel Data Nasabah
Kolom yang ditampilkan:
- No. Identitas
- Nama Lengkap
- No. Telepon
- Transaksi Tabungan Terakhir
- Transaksi Pinjaman Terakhir
- Jumlah Transaksi Tabungan
- Jumlah Transaksi Pinjaman
- Total Transaksi

### 4. Fitur Tambahan
- **Export PDF**: Ekspor laporan ke format PDF
- **Refresh Data**: Memperbarui data pada halaman
- **Detail Nasabah**: Akses detail profil nasabah (klik tombol Detail)
- **Pencarian**: Cari berdasarkan no. identitas atau nama
- **Sorting**: Urutkan berdasarkan kolom yang tersedia
- **Filter Tanggal**: Filter data berdasarkan range tanggal

## Cara Penggunaan

### Mengakses Halaman
1. Login ke Admin Panel (`/admin`)
2. Navigasi ke menu **Laporan** → **Nasabah Aktif**

### Melihat Laporan Default
- Secara default, halaman menampilkan nasabah dengan transaksi dalam 90 hari terakhir
- Data ditampilkan dalam bentuk tabel dengan informasi lengkap

### Filtering Data
1. Gunakan form filter di bagian atas halaman
2. Pilih tanggal mulai dan tanggal selesai
3. Pilih jenis transaksi (Semua/Tabungan/Pinjaman)
4. Klik di luar form atau tekan Enter untuk apply filter

### Export ke PDF
1. Klik tombol **Export PDF** di pojok kanan atas
2. PDF akan otomatis ter-download dengan nama:
   `laporan-nasabah-aktif-YYYY-MM-DD-HH-mm-ss.pdf`
3. PDF berisi:
   - Header laporan dengan periode
   - Statistik ringkasan
   - Tabel data lengkap nasabah aktif

### Melihat Detail Nasabah
1. Pada tabel, klik tombol **Detail** di kolom Actions
2. Akan membuka halaman profil nasabah di tab baru

## Informasi Teknis

### Kriteria Nasabah Aktif
Nasabah dianggap aktif jika memiliki minimal 1 transaksi (tabungan atau pinjaman) dalam periode yang ditentukan.

### Sumber Data
- **TransaksiTabungan**: Data transaksi tabungan (debit/kredit)
  - Field: `tanggal_transaksi`
- **TransaksiPinjaman**: Data transaksi pembayaran pinjaman
  - Field: `tanggal_pembayaran`

### Query Performance
- Menggunakan LEFT JOIN untuk menggabungkan data tabungan dan pinjaman
- Aggregasi dengan COUNT DISTINCT untuk menghitung jumlah transaksi
- Grouping berdasarkan profile untuk menghindari duplikasi
- Index pada kolom tanggal transaksi direkomendasikan untuk performa optimal

## Troubleshooting

### Data Tidak Muncul
- Pastikan ada transaksi dalam periode yang dipilih
- Cek filter jenis transaksi yang dipilih
- Refresh data dengan tombol Refresh

### Export PDF Error
- Pastikan DOMPDF terinstall dengan benar
- Cek permission folder storage
- Cek Laravel logs untuk error detail

### Jumlah Tidak Sesuai
- Pastikan periode tanggal sudah benar
- Perhatikan perbedaan antara "Semua Transaksi" vs filter spesifik
- Transaksi dihitung per nasabah, bukan per rekening

## Lokasi File

- **Controller**: `app/Filament/Pages/LaporanNasabahAktif.php`
- **View**: `resources/views/filament/pages/laporan-nasabah-aktif.blade.php`
- **PDF Template**: `resources/views/pdf/laporan-nasabah-aktif.blade.php`

## Model yang Digunakan

- `App\Models\Profile` - Data nasabah
- `App\Models\Tabungan` - Rekening tabungan
- `App\Models\TransaksiTabungan` - Transaksi tabungan
- `App\Models\Pinjaman` - Data pinjaman
- `App\Models\TransaksiPinjaman` - Transaksi pinjaman