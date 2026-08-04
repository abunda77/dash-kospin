# Spesifikasi Workflow Setoran Anggota via Dynamic QRIS

## 1. Tujuan

Dokumen ini menjadi panduan implementasi bagi AI coding agent untuk membangun fitur setoran anggota melalui Dynamic QRIS tanpa webhook atau akses API status transaksi.

Sistem menggunakan alur berikut:

1. Anggota memilih rekening tabungan tujuan.
2. Anggota memasukkan nominal setoran.
3. Sistem membuat transaksi setoran lokal.
4. Sistem menghasilkan Dynamic QRIS berdasarkan nominal setoran ditambah kode unik.
5. Anggota melakukan pembayaran.
6. Anggota menekan tombol **Saya Sudah Membayar** dan dapat mengunggah bukti pembayaran.
7. Admin memverifikasi pembayaran secara manual melalui dashboard penyedia QRIS.
8. Jika valid, sistem memposting saldo ke rekening tabungan anggota.
9. Jika tidak valid, admin menolak pengajuan beserta alasannya.

> Tombol **Saya Sudah Membayar** merupakan klaim dari pengguna, bukan bukti final bahwa pembayaran telah diterima.

---

## 2. Ruang Lingkup

Fitur ini mencakup:

- Pemilihan rekening tabungan anggota.
- Pembuatan transaksi setoran.
- Pembuatan kode unik.
- Pembuatan Dynamic QRIS.
- Konfirmasi pembayaran oleh anggota.
- Upload bukti pembayaran.
- Verifikasi manual oleh admin.
- Posting saldo ke rekening tabungan anggota.
- Pencatatan transaksi tabungan (mutasi).
- Audit trail perubahan status.
- Pencegahan saldo masuk dua kali.
- Penanganan transaksi kedaluwarsa, ditolak, dan diajukan ulang.

Fitur ini tidak mencakup:

- Webhook otomatis dari penyedia QRIS.
- Pemeriksaan status melalui API provider.
- Penambahan saldo otomatis segera setelah QRIS dibayar.
- Verifikasi pembayaran berdasarkan screenshot.

---

## 3. Prinsip Utama

### 3.1 Record dibuat sebelum QRIS ditampilkan

Record setoran wajib dibuat sebelum Dynamic QRIS dihasilkan.

Tujuannya:

- Menyimpan nomor setoran.
- Menentukan kode unik secara aman.
- Menghubungkan transaksi dengan rekening tujuan.
- Menyimpan nominal yang harus dibayar.
- Menentukan waktu kedaluwarsa.
- Mencegah QRIS tanpa transaksi lokal.
- Mempermudah audit dan penanganan transaksi gagal.

### 3.2 Pisahkan nominal setoran dan total pembayaran

Gunakan tiga nilai berbeda:

```text
jumlah = nominal yang masuk ke saldo anggota
kode_unik = kode unik untuk pencocokan manual
jumlah_bayar = jumlah + kode_unik
```

Contoh:

```text
jumlah = 100000
kode_unik = 37
jumlah_bayar = 100037
```

Saldo anggota tidak boleh dihitung langsung dari `jumlah_bayar` kecuali kebijakan bisnis secara eksplisit menyatakan kode unik ikut dikreditkan.

### 3.3 Saldo berubah setelah verifikasi admin

Tindakan berikut tidak boleh menambah saldo:

- QRIS berhasil dibuat.
- Pengguna membuka QRIS.
- Pengguna menekan tombol **Saya Sudah Membayar**.
- Pengguna mengunggah bukti pembayaran.

Saldo boleh berubah setelah:

1. Admin menemukan transaksi yang sesuai pada dashboard provider QRIS.
2. Admin menyetujui pembayaran.
3. Sistem berhasil membuat record `TransaksiTabungan` secara atomic.

### 3.4 Posting saldo harus idempotent

Satu setoran hanya boleh menghasilkan satu record `TransaksiTabungan`.

Gunakan:

- Database transaction.
- Row locking.
- Unique constraint pada `setoran_id` di tabel `transaksi_tabungans`.
- Pemeriksaan status sebelum posting.
- Audit trail.

---

## 4. Aktor Sistem

### 4.1 Anggota

Anggota (`App\Models\User`, guard `web`, panel `/user`) dapat:

- Melihat rekening tabungan miliknya.
- Memilih rekening tujuan setoran.
- Memasukkan nominal.
- Membuat QRIS pembayaran.
- Melihat detail transaksi.
- Menekan tombol **Saya Sudah Membayar**.
- Mengunggah bukti pembayaran.
- Melihat status verifikasi.
- Memperbaiki bukti jika diminta.
- Melihat saldo dan mutasi setelah setoran disetujui.

### 4.2 Admin

Admin (`App\Models\Admin`, guard `admin`, panel `/admin`) dapat:

- Melihat daftar pengajuan setoran.
- Memfilter berdasarkan status.
- Membuka detail setoran.
- Melihat bukti pembayaran.
- Membandingkan data dengan dashboard provider QRIS.
- Mengambil alih proses verifikasi.
- Menyetujui dan memposting saldo.
- Menolak pengajuan.
- Meminta perbaikan bukti.
- Melihat audit trail.
- Mencoba ulang posting saldo yang gagal.

---

## 5. Status Transaksi

Gunakan enum atau konstanta status berikut:

| Status | Arti |
|---|---|
| `menunggu_pembayaran` | QRIS sudah dibuat, pengguna belum mengonfirmasi pembayaran |
| `menunggu_verifikasi` | Pengguna mengaku sudah membayar |
| `sedang_diperiksa` | Admin sedang memeriksa pembayaran |
| `perlu_revisi` | Admin meminta pengguna memperbaiki data atau bukti |
| `disetujui` | Pembayaran sudah ditemukan dan dinyatakan valid |
| `selesai` | Transaksi tabungan berhasil diposting |
| `ditolak` | Pembayaran tidak ditemukan atau tidak valid |
| `kedaluwarsa` | Masa aktif QRIS berakhir tanpa konfirmasi pembayaran |
| `dibatalkan` | Transaksi dibatalkan sebelum diproses |

### 5.1 Transisi status yang diizinkan

```text
menunggu_pembayaran
├── menunggu_verifikasi
├── kedaluwarsa
└── dibatalkan

menunggu_verifikasi
├── sedang_diperiksa
├── perlu_revisi
└── ditolak

perlu_revisi
├── menunggu_verifikasi
├── dibatalkan
└── ditolak

sedang_diperiksa
├── disetujui
├── perlu_revisi
└── ditolak

disetujui
└── selesai
```

Transaksi `selesai` tidak boleh dikembalikan ke status sebelumnya melalui operasi normal.

Koreksi setelah `selesai` harus dilakukan melalui transaksi koreksi atau reversal baru, bukan dengan menghapus record `TransaksiTabungan` lama.

---

## 6. Workflow Utama

## 6.1 Pemilihan rekening tujuan

1. Pengguna harus login (guard `web`).
2. Sistem menampilkan rekening tabungan aktif milik pengguna.
3. Pengguna memilih satu rekening.
4. Backend wajib memvalidasi bahwa:
   - Rekening benar-benar milik pengguna.
   - Rekening berstatus `aktif`.
   - Anggota berstatus aktif (`profiles.is_active = true`).
   - Rekening tidak diblokir atau ditutup.

Relasi kepemilikan rekening:

```text
users (id)
  └── profiles (id_user → users.id)   ← PK Eloquent = id_user
        └── tabungans (id_profile → profiles.id)   ← profiles.id, bukan id_user!
```

Contoh validasi Laravel:

```php
$tabungan = Tabungan::query()
    ->whereHas('profile', fn ($q) => $q
        ->where('id_user', auth()->id())
        ->where('is_active', true)
    )
    ->whereKey($request->id_tabungan)
    ->where('status_rekening', 'aktif')
    ->firstOrFail();
```

Backend tidak boleh mempercayai `id_tabungan` dari frontend tanpa validasi kepemilikan.

---

## 6.2 Input nominal setoran

Pengguna memasukkan nominal setoran.

Validasi minimal:

```text
required
integer
minimum sesuai kebijakan
maximum sesuai kebijakan
```

Contoh:

```php
$request->validate([
    'id_tabungan' => ['required', 'integer'],
    'jumlah' => ['required', 'integer', 'min:10000', 'max:100000000'],
]);
```

Nominal harus menggunakan integer dalam satuan rupiah.

Hindari penggunaan floating point untuk nilai uang.

---

## 6.3 Pemeriksaan transaksi aktif

Sebelum membuat transaksi baru, sistem memeriksa apakah anggota masih memiliki transaksi aktif.

Contoh status aktif:

```text
menunggu_pembayaran
menunggu_verifikasi
sedang_diperiksa
perlu_revisi
disetujui
```

Kebijakan yang disarankan:

- Maksimal satu transaksi aktif per rekening tabungan.
- Atau maksimal tiga transaksi aktif per anggota.

Jika transaksi `menunggu_pembayaran` masih aktif, sistem dapat menampilkan transaksi tersebut daripada membuat QRIS baru.

---

## 6.4 Pembuatan nomor setoran

Nomor setoran harus unik dan mudah dibaca.

Format yang disarankan:

```text
STR-YYYYMMDD-XXXXXX
```

Contoh:

```text
STR-20260804-000123
```

Gunakan unique constraint pada database.

Nomor setoran tidak boleh bergantung pada jumlah baris database jika ada kemungkinan concurrency.

---

## 6.5 Pembuatan kode unik

Kode unik membantu admin mencocokkan pembayaran pada dashboard provider.

Rentang yang disarankan:

```text
1 sampai 999
```

Algoritma:

1. Generate angka acak.
2. Hitung `jumlah_bayar`.
3. Pastikan `jumlah_bayar` belum digunakan transaksi aktif lain.
4. Ulangi jika terjadi benturan.
5. Simpan kode unik bersama transaksi.

Contoh:

```php
do {
    $kodeUnik = random_int(1, 999);
    $jumlahBayar = $jumlah + $kodeUnik;

    $exists = SetoranTabungan::query()
        ->where('jumlah_bayar', $jumlahBayar)
        ->whereIn('status', [
            'menunggu_pembayaran',
            'menunggu_verifikasi',
            'sedang_diperiksa',
            'perlu_revisi',
            'disetujui',
        ])
        ->where('kedaluwarsa_at', '>', now())
        ->exists();
} while ($exists);
```

Kode unik adalah alat bantu pencocokan manual, bukan bukti keamanan pembayaran.

---

## 6.6 Pembuatan record setoran

Buat record setoran sebelum Dynamic QRIS dibuat.

Status awal:

```text
menunggu_pembayaran
```

Data minimal:

```text
nomor_setoran
user_id              ← FK ke users.id
id_tabungan          ← FK ke tabungans.id
jenis_simpanan       ← dari produkTabungan anggota
jumlah
kode_unik
jumlah_bayar
status
qris_dibuat_at
kedaluwarsa_at
```

Contoh:

```php
$setoran = SetoranTabungan::create([
    'nomor_setoran'  => $nomorSetoran,
    'user_id'        => auth()->id(),
    'id_tabungan'    => $tabungan->id,
    'jenis_simpanan' => $tabungan->produkTabungan->nama_produk,
    'jumlah'         => $jumlah,
    'kode_unik'      => $kodeUnik,
    'jumlah_bayar'   => $jumlahBayar,
    'status'         => StatusSetoran::MENUNGGU_PEMBAYARAN,
    'qris_dibuat_at' => now(),
    'kedaluwarsa_at' => now()->addMinutes(config('setoran.durasi_qris', 30)),
]);
```

Pembuatan transaksi dan alokasi kode unik sebaiknya dilakukan di dalam database transaction.

---

## 6.7 Pembuatan Dynamic QRIS

Project sudah memiliki infrastruktur QRIS lengkap:

- `App\Models\QrisStatic` — menyimpan payload QRIS statis koperasi.
- `App\Http\Controllers\Api\PaymentController::buildDynamicQris()` — konversi statis ke dinamis sesuai standar ASPI/EMVCo.
- Library `endroid/qr-code` — generate gambar PNG QR Code.

Dynamic QRIS harus dibuat menggunakan:

```text
jumlah_bayar
```

Bukan:

```text
jumlah
```

Langkah pembuatan:

1. Ambil record `QrisStatic` aktif dari database.
2. Panggil `buildDynamicQris($staticQris, $jumlahBayar, 'Rupiah', 0)`.
3. Generate gambar PNG menggunakan `endroid/qr-code` dan simpan ke storage.

Penyimpanan gambar QRIS menggunakan disk `public` (bukan private), karena gambar QR perlu ditampilkan langsung kepada anggota:

```text
storage/app/public/qris-generated/qris-dynamic-{YmdHis}-{uniqid}.png
```

Data QRIS yang disimpan ke tabel `setoran_tabungans`:

```text
qris_payload          ← string payload dinamis
qris_image_path       ← path relatif di storage public
qris_dibuat_at        ← waktu pembuatan
kedaluwarsa_at        ← waktu kedaluwarsa
```

Jangan menyimpan `qris_string` statis milik koperasi di frontend.

---

## 6.8 Tampilan pembayaran kepada pengguna

Halaman pembayaran harus menampilkan:

```text
Nomor setoran
Rekening tujuan
Jenis simpanan
Nominal setoran
Kode unik
Total pembayaran
Dynamic QRIS
Waktu kedaluwarsa
Status transaksi
```

Contoh:

```text
Nomor setoran: STR-20260804-000123
Rekening tujuan: 00123-02
Nominal: Rp100.000
Kode unik: Rp37
Total dibayar: Rp100.037
Berlaku sampai: 18:30 WIB
```

Gunakan label tombol:

```text
Saya Sudah Membayar
```

Hindari label:

```text
Saya Sudah Transfer
```

QRIS tidak selalu dibayar melalui transfer bank.

---

## 6.9 Konfirmasi pembayaran oleh pengguna

Setelah membayar, pengguna menekan tombol **Saya Sudah Membayar**.

Form dapat berisi:

| Field | Aturan |
|---|---|
| `waktu_klaim_bayar` | Wajib |
| `nama_pembayar` | Wajib |
| `referensi_pembayaran` | Opsional |
| `bukti_pembayaran` | Wajib pada tahap awal |
| `catatan_pengguna` | Opsional |

Contoh validasi:

```php
$request->validate([
    'waktu_klaim_bayar'    => ['required', 'date'],
    'nama_pembayar'        => ['required', 'string', 'max:100'],
    'referensi_pembayaran' => ['nullable', 'string', 'max:100'],
    'bukti_pembayaran'     => [
        'required',
        'file',
        'mimes:jpg,jpeg,png,pdf',
        'max:4096',
    ],
    'catatan_pengguna' => ['nullable', 'string', 'max:500'],
]);
```

Saat dikirim:

```text
menunggu_pembayaran -> menunggu_verifikasi
```

Simpan:

```text
waktu_klaim_bayar
nama_pembayar
referensi_pembayaran
bukti_pembayaran_path
catatan_pengguna
dikirim_at
is_terlambat
```

Tombol ini boleh diproses untuk transaksi yang berada dalam status:

```text
menunggu_pembayaran
perlu_revisi
```

---

## 6.10 Konfirmasi setelah QRIS kedaluwarsa

Pengguna membayar sebelum QRIS kedaluwarsa tetapi baru menekan tombol setelah waktu berakhir.

Aturan:

```text
Jika waktu_klaim_bayar <= kedaluwarsa_at:
    proses sebagai menunggu_verifikasi

Jika waktu_klaim_bayar > kedaluwarsa_at:
    proses sebagai menunggu_verifikasi
    set is_terlambat = true
```

Admin wajib memeriksa waktu transaksi pada dashboard provider.

Jangan langsung menolak karena pengguna mengirim konfirmasi setelah `kedaluwarsa_at`.

---

## 6.11 Proses verifikasi admin

Admin membuka daftar transaksi dengan status:

```text
menunggu_verifikasi
```

Saat admin mulai memeriksa:

```text
menunggu_verifikasi -> sedang_diperiksa
```

Simpan:

```text
mulai_review_at
diperiksa_oleh   ← FK ke admins.id
```

Dashboard admin harus menampilkan:

```text
Nomor setoran
Nama anggota
Nomor rekening tabungan
Jenis simpanan
Nominal setoran
Kode unik
Total pembayaran
Tanggal dan jam klaim pembayaran
Nama pembayar
Referensi pembayaran
Bukti pembayaran
Waktu kedaluwarsa
Status
Riwayat status
```

Admin mencocokkan data tersebut dengan dashboard provider QRIS.

Pencocokan minimal:

```text
jumlah_bayar
tanggal transaksi
waktu transaksi
nama pembayar jika tersedia
referensi transaksi jika tersedia
status transaksi provider
```

Screenshot pembayaran tidak boleh menjadi satu-satunya dasar persetujuan.

---

## 6.12 Persetujuan pembayaran

Jika transaksi ditemukan dan valid:

```text
sedang_diperiksa -> disetujui
```

Simpan:

```text
disetujui_at
direview_at
direview_oleh              ← FK ke admins.id
referensi_transaksi_provider
waktu_bayar_provider
nama_pembayar_provider
catatan_verifikasi
```

Setelah status `disetujui`, sistem memposting saldo.

Posting saldo dapat dilakukan:

- Langsung dalam request yang sama.
- Melalui service khusus.
- Melalui queue job.

Jika posting saldo gagal, status tetap `disetujui` agar dapat dicoba ulang tanpa memverifikasi pembayaran dari awal.

---

## 6.13 Posting saldo

Posting saldo membuat record `TransaksiTabungan` dengan `jenis_transaksi = 'debit'` (setoran), mengikuti konvensi yang sudah ada di model `TransaksiTabungan`.

Saldo `Tabungan` dihitung dari akumulasi seluruh `TransaksiTabungan` — bukan dari kolom cached. Kolom `saldo` di tabel `tabungans` adalah saldo awal/dasar, dan saldo aktual diperoleh via accessor `getSaldoAkhirAttribute()`.

Posting saldo harus menggunakan database transaction dan row lock.

Contoh:

```php
DB::transaction(function () use ($setoranId, $adminId) {
    $setoran = SetoranTabungan::query()
        ->lockForUpdate()
        ->findOrFail($setoranId);

    if ($setoran->status === StatusSetoran::SELESAI) {
        return;
    }

    if ($setoran->status !== StatusSetoran::DISETUJUI) {
        throw new RuntimeException('Setoran belum disetujui.');
    }

    $tabungan = Tabungan::query()
        ->lockForUpdate()
        ->findOrFail($setoran->id_tabungan);

    $sudahAdaTransaksi = TransaksiTabungan::query()
        ->where('setoran_id', $setoran->id)
        ->exists();

    if ($sudahAdaTransaksi) {
        throw new RuntimeException('Setoran sudah memiliki transaksi tabungan.');
    }

    TransaksiTabungan::create([
        'id_tabungan'      => $tabungan->id,
        'setoran_id'       => $setoran->id,
        'jenis_transaksi'  => TransaksiTabungan::JENIS_SETORAN,
        'jumlah'           => $setoran->jumlah,
        'tanggal_transaksi'=> now(),
        'keterangan'       => 'Setoran via QRIS - ' . $setoran->nomor_setoran,
        'kode_transaksi'   => $setoran->nomor_setoran,
        'kode_teller'      => $adminId,
    ]);

    $setoran->update([
        'status'      => StatusSetoran::SELESAI,
        'diposting_at'=> now(),
        'selesai_at'  => now(),
    ]);
});
```

> Catatan: Kolom `setoran_id` perlu ditambahkan ke tabel `transaksi_tabungans` via migration baru dengan unique constraint.

Setelah berhasil:

```text
disetujui -> selesai
```

---

## 6.14 Penolakan pembayaran

Admin dapat menolak transaksi dari status:

```text
menunggu_verifikasi
sedang_diperiksa
```

Status menjadi:

```text
ditolak
```

Alasan wajib diisi.

Contoh alasan:

```text
Pembayaran tidak ditemukan
Nominal tidak sesuai
Waktu transaksi tidak sesuai
Bukti pembayaran tidak terbaca
Pembayaran sudah digunakan untuk transaksi lain
Referensi transaksi tidak valid
Data pembayar tidak cocok
Pembayaran dilakukan setelah QRIS tidak berlaku
```

Simpan:

```text
ditolak_at
ditolak_oleh    ← FK ke admins.id
alasan_penolakan
```

---

## 6.15 Permintaan perbaikan bukti

Jika data belum cukup tetapi belum layak ditolak:

```text
sedang_diperiksa -> perlu_revisi
```

Admin wajib menulis alasan atau instruksi perbaikan.

Contoh:

```text
unggah ulang bukti pembayaran yang menampilkan nominal,
tanggal, waktu, dan nomor referensi transaksi.
```

Pengguna dapat mengirim ulang data:

```text
perlu_revisi -> menunggu_verifikasi
```

Setiap versi bukti lama sebaiknya tetap tersimpan atau dicatat dalam audit trail.

---

## 7. Struktur Database

## 7.1 Tabel `setoran_tabungans` (model baru)

Kolom yang disarankan:

```text
id
nomor_setoran

user_id              ← FK ke users.id
id_tabungan          ← FK ke tabungans.id
jenis_simpanan       ← nama produk tabungan

jumlah
kode_unik
jumlah_bayar

qris_payload
qris_image_path      ← path di storage public (qris-generated/)
qris_dibuat_at
kedaluwarsa_at

status

waktu_klaim_bayar
nama_pembayar
referensi_pembayaran
bukti_pembayaran_path
catatan_pengguna
dikirim_at
is_terlambat

mulai_review_at
direview_at
diperiksa_oleh       ← FK ke admins.id

referensi_transaksi_provider
waktu_bayar_provider
nama_pembayar_provider
catatan_verifikasi

disetujui_at
ditolak_at
ditolak_oleh         ← FK ke admins.id
alasan_penolakan

diposting_at
selesai_at

created_at
updated_at
deleted_at
```

Index yang disarankan:

```php
$table->string('nomor_setoran')->unique();

$table->index(['user_id', 'status']);
$table->index(['id_tabungan', 'status']);
$table->index(['jumlah_bayar', 'status']);
$table->index(['kedaluwarsa_at', 'status']);
$table->index('referensi_pembayaran');
$table->index('referensi_transaksi_provider');
```

---

## 7.2 Tabel `tabungans` (sudah ada)

Kolom yang relevan:

```text
id
no_tabungan          ← nomor rekening, unique
id_profile           ← FK ke profiles.id (bukan profiles.id_user!)
produk_tabungan      ← FK ke produk_tabungans.id
saldo                ← saldo awal/dasar (decimal 15,2)
tanggal_buka_rekening
status_rekening      ← enum: aktif / ditutup
notes
```

Saldo aktual anggota dihitung via accessor `getSaldoAkhirAttribute()` di model `Tabungan`, bukan dari kolom `saldo` secara langsung. Sumber kebenaran saldo tetap pada tabel `transaksi_tabungans`.

---

## 7.3 Tabel `transaksi_tabungans` (sudah ada, perlu satu kolom tambahan)

Kolom yang sudah ada:

```text
id
id_tabungan          ← FK ke tabungans.id
jenis_transaksi      ← enum: debit (setoran) / kredit (penarikan)
jumlah               ← decimal 15,2
tanggal_transaksi
keterangan
kode_transaksi
kode_teller          ← FK ke admins.id (nullable)
```

Kolom tambahan yang perlu ditambah via migration baru:

```php
$table->foreignId('setoran_id')
      ->nullable()
      ->unique()
      ->constrained('setoran_tabungans')
      ->nullOnDelete();
```

Unique constraint pada `setoran_id` memastikan satu setoran hanya menghasilkan satu transaksi tabungan.

---

## 7.4 Tabel `riwayat_status_setorans`

Kolom yang disarankan:

```text
id
setoran_id
status_sebelumnya
status_baru
diubah_oleh_type     ← App\Models\User atau App\Models\Admin
diubah_oleh_id
catatan
metadata             ← JSON, data tambahan jika perlu
ip_address
user_agent
created_at
```

Setiap perubahan status wajib membuat record histori.

Contoh:

```text
10:03 menunggu_pembayaran -> menunggu_verifikasi oleh anggota
10:15 menunggu_verifikasi -> sedang_diperiksa oleh admin
10:20 sedang_diperiksa -> disetujui oleh admin
10:20 disetujui -> selesai oleh sistem
```

---

## 7.5 Tabel `bukti_setorans`

Gunakan tabel terpisah jika bukti dapat diunggah lebih dari sekali.

Kolom:

```text
id
setoran_id
file_path
nama_asli
mime_type
ukuran_file
diunggah_oleh_type
diunggah_oleh_id
is_terkini
created_at
```

File lama tidak perlu langsung dihapus agar histori tetap dapat diaudit.

Bukti pembayaran disimpan di disk `private`:

```php
$path = $request->file('bukti_pembayaran')
    ->store(
        "setoran-tabungan/{$setoran->nomor_setoran}",
        'private'
    );
```

---

## 8. Enum Status Laravel

Contoh:

```php
<?php

namespace App\Enums;

enum StatusSetoran: string
{
    case MENUNGGU_PEMBAYARAN  = 'menunggu_pembayaran';
    case MENUNGGU_VERIFIKASI  = 'menunggu_verifikasi';
    case SEDANG_DIPERIKSA     = 'sedang_diperiksa';
    case PERLU_REVISI         = 'perlu_revisi';
    case DISETUJUI            = 'disetujui';
    case SELESAI              = 'selesai';
    case DITOLAK              = 'ditolak';
    case KEDALUWARSA          = 'kedaluwarsa';
    case DIBATALKAN           = 'dibatalkan';
}
```

Model:

```php
protected function casts(): array
{
    return [
        'status'             => StatusSetoran::class,
        'jumlah'             => 'integer',
        'kode_unik'          => 'integer',
        'jumlah_bayar'       => 'integer',
        'qris_dibuat_at'     => 'datetime',
        'kedaluwarsa_at'     => 'datetime',
        'waktu_klaim_bayar'  => 'datetime',
        'dikirim_at'         => 'datetime',
        'direview_at'        => 'datetime',
        'disetujui_at'       => 'datetime',
        'ditolak_at'         => 'datetime',
        'diposting_at'       => 'datetime',
        'selesai_at'         => 'datetime',
        'is_terlambat'       => 'boolean',
    ];
}
```

---

## 9. Service yang Disarankan

Pisahkan logika bisnis ke service berikut:

```text
BuatSetoranTabunganService
GenerateJumlahBayarUnikService
GenerateDynamicQrisSetoranService   ← menggunakan QrisStatic + buildDynamicQris yang sudah ada
KirimKlaimPembayaranService
MulaiReviewSetoranService
SetujuiSetoranService
TolakSetoranService
MintaRevisiSetoranService
PostingSetoranKeTabunganService
KadaluarsaSetoranTidakDibayarService
CatatRiwayatStatusSetoranService
```

`GenerateDynamicQrisSetoranService` menggunakan logika yang sudah ada di `PaymentController::buildDynamicQris()` dan library `endroid/qr-code`. Ekstrak atau duplikasi logika tersebut ke dalam service agar tidak bergantung pada controller.

Controller bertanggung jawab untuk:

- Authorization.
- Validasi request.
- Memanggil service.
- Mengembalikan response.

Jangan menaruh seluruh logika transaksi di controller.

---

## 10. Authorization

Gunakan Policy Laravel.

### Anggota boleh:

```text
view
kirimKlaim
unggahBukti
ajukanUlang
batalkan
```

jika transaksi miliknya (`user_id = auth()->id()`).

### Admin boleh:

```text
viewAny
mulaiReview
setujui
tolak
mintaRevisi
cobaUlangPosting
```

Authorization harus diperiksa di backend, bukan melalui visibilitas tombol di frontend.

---

## 11. Endpoint yang Disarankan

Endpoint ini untuk panel anggota (`/user`) dan admin (`/admin`) via Filament atau Livewire. Jika diekspos sebagai REST API terpisah, ikuti pola yang ada di `routes/api.php`.

### Endpoint anggota

```http
GET /api/member/tabungans
POST /api/member/setorans
GET /api/member/setorans
GET /api/member/setorans/{setoran}
POST /api/member/setorans/{setoran}/klaim-pembayaran
POST /api/member/setorans/{setoran}/bukti-pembayaran
POST /api/member/setorans/{setoran}/batalkan
```

### Endpoint admin

```http
GET /api/admin/setorans
GET /api/admin/setorans/{setoran}
POST /api/admin/setorans/{setoran}/mulai-review
POST /api/admin/setorans/{setoran}/setujui
POST /api/admin/setorans/{setoran}/tolak
POST /api/admin/setorans/{setoran}/minta-revisi
POST /api/admin/setorans/{setoran}/coba-ulang-posting
```

Gunakan route model binding dan Policy.

---

## 12. Scheduler

Gunakan scheduler untuk mengubah transaksi tidak dibayar menjadi `kedaluwarsa`.

Contoh:

```php
Schedule::command('setorans:kadaluarsa-tidak-dibayar')
    ->everyMinute()
    ->withoutOverlapping();
```

Command memproses:

```text
status = menunggu_pembayaran
kedaluwarsa_at < now
```

Jangan mengubah transaksi berikut menjadi kedaluwarsa:

```text
menunggu_verifikasi
sedang_diperiksa
disetujui
```

Contoh query:

```php
SetoranTabungan::query()
    ->where('status', StatusSetoran::MENUNGGU_PEMBAYARAN)
    ->where('kedaluwarsa_at', '<', now())
    ->chunkById(100, function ($setorans) {
        foreach ($setorans as $setoran) {
            $statusLama = $setoran->status;
            $setoran->update([
                'status' => StatusSetoran::KEDALUWARSA,
            ]);
            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                $statusLama,
                StatusSetoran::KEDALUWARSA,
                null,
                'Sistem',
                'Kedaluwarsa otomatis oleh scheduler'
            );
        }
    });
```

Pastikan setiap perubahan dicatat dalam riwayat status.

---

## 13. Pencegahan Fraud dan Duplikasi

Sistem harus memiliki kontrol berikut:

1. Pengguna hanya dapat memilih rekening tabungan miliknya sendiri (divalidasi via relasi `users → profiles → tabungans`).
2. Nominal tidak dapat diubah setelah QRIS dibuat.
3. Total pembayaran (`jumlah_bayar`) dihitung oleh backend.
4. Kode unik ditentukan backend.
5. Bukti pembayaran tidak dianggap bukti final.
6. Admin wajib mencocokkan dengan dashboard provider QRIS.
7. Referensi provider dicatat jika tersedia.
8. Satu referensi provider tidak boleh dipakai dua kali.
9. Satu setoran hanya boleh menghasilkan satu record `TransaksiTabungan` (unique constraint pada `setoran_id`).
10. Posting saldo harus menggunakan row lock (`lockForUpdate()`).
11. Semua perubahan status dicatat di `riwayat_status_setorans`.
12. Tindakan admin mencatat user ID, waktu, IP, dan catatan.
13. File upload dibatasi tipe dan ukuran.
14. Bukti pembayaran disimpan pada private storage.
15. Gambar QRIS disimpan pada public storage (hanya dapat diakses oleh anggota pemilik via controller).
16. Endpoint anggota dan admin menggunakan rate limiting.
17. Transaksi `selesai` tidak boleh diedit langsung.
18. Koreksi dilakukan melalui transaksi `TransaksiTabungan` reversal terpisah.

---

## 14. Penyimpanan File

### Bukti pembayaran (private)

Bukti pembayaran harus disimpan di disk `private`:

```php
$path = $request->file('bukti_pembayaran')
    ->store(
        "setoran-tabungan/{$setoran->nomor_setoran}",
        'private'
    );
```

Jangan menyimpan bukti pembayaran di direktori publik tanpa authorization.

Untuk menampilkan file:

1. Periksa Policy.
2. Stream file melalui controller.
3. Gunakan signed URL jika storage mendukung.
4. Catat akses sensitif jika diperlukan.

### Gambar QRIS (public)

Gambar Dynamic QRIS disimpan di disk `public` mengikuti pola yang sudah ada:

```text
storage/app/public/qris-generated/qris-dynamic-{YmdHis}-{uniqid}.png
```

Gambar QRIS hanya ditampilkan kepada anggota pemilik transaksi — periksa kepemilikan di controller sebelum mengembalikan URL.

---

## 15. Notifikasi Aplikasi

### Notifikasi kepada admin

Kirim notifikasi ketika:

```text
Pengguna mengirim klaim pembayaran
Pengguna memperbarui bukti
Pengajuan menunggu terlalu lama
Posting saldo gagal
```

### Notifikasi kepada anggota

Kirim notifikasi ketika:

```text
QRIS berhasil dibuat
Klaim pembayaran diterima
Admin mulai memeriksa
Perbaikan bukti diperlukan
Pembayaran disetujui
Saldo berhasil ditambahkan
Pengajuan ditolak
Transaksi kedaluwarsa
```

Gunakan kalimat:

```text
Konfirmasi pembayaran berhasil dikirim.
```

Jangan gunakan:

```text
Pembayaran berhasil.
```

sebelum pembayaran benar-benar diverifikasi.

---

## 15.1 Notifikasi WhatsApp ke Admin via WhatsApp Gateway

Setelah anggota menekan tombol **Saya Sudah Membayar** dan status berubah menjadi `menunggu_verifikasi`, sistem wajib mengirim notifikasi WhatsApp ke nomor admin menggunakan WhatsApp Gateway non-official berbasis `go-whatsapp-web-multidevice`.

### Konfigurasi

Tambahkan ke `config/whatsapp_gateway.php` dan daftarkan env vars di `.env.example`:

```php
return [
    'base_url'  => env('WA_GATEWAY_URL', 'http://127.0.0.1:3000'),
    'username'  => env('WA_GATEWAY_USERNAME', ''),
    'password'  => env('WA_GATEWAY_PASSWORD', ''),
    'device_id' => env('WA_GATEWAY_DEVICE_ID', ''),
    'admin_wa'  => env('WA_ADMIN_NOTIF_NUMBER', ''),
    'timeout'   => env('WA_GATEWAY_TIMEOUT', 10),
];
```

`.env.example`:

```env
WA_GATEWAY_URL=http://127.0.0.1:3000
WA_GATEWAY_USERNAME=
WA_GATEWAY_PASSWORD=
WA_GATEWAY_DEVICE_ID=
WA_ADMIN_NOTIF_NUMBER=
WA_GATEWAY_TIMEOUT=10
```

- `WA_ADMIN_NOTIF_NUMBER` — nomor WhatsApp admin dalam format JID: `628xxxxxxxxxx@s.whatsapp.net`.
- `WA_GATEWAY_DEVICE_ID` — device ID perangkat WhatsApp yang terdaftar di gateway.
- Jangan menaruh credential gateway di source code. Selalu ambil dari `config()`.

### Endpoint Gateway

```http
POST /send/message
Host: {WA_GATEWAY_URL}
Authorization: Basic {base64(username:password)}
X-Device-Id: {WA_GATEWAY_DEVICE_ID}
Content-Type: application/json
```

Request body:

```json
{
    "phone": "628xxxxxxxxxx@s.whatsapp.net",
    "message": "...",
    "reply_message_id": "",
    "is_forwarded": false
}
```

### Format Pesan Notifikasi

Pesan yang dikirim ke admin saat anggota menekan **Saya Sudah Membayar**:

```text
[KLAIM SETORAN MASUK]

Nomor Setoran : STR-20260805-000123
Nama Anggota  : Budi Santoso
No. Rekening  : 00123-02
Nominal       : Rp100.000
Kode Unik     : Rp37
Total Bayar   : Rp100.037
Waktu Klaim   : 05/08/2026 12:34 WIB
Nama Pembayar : Budi Santoso

Silakan periksa dashboard admin untuk memverifikasi pembayaran.
```

### Service Pengiriman

Buat `App\Services\WhatsAppGatewayService`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    public function kirimPesan(string $nomorTujuan, string $pesan): bool
    {
        try {
            $response = Http::withBasicAuth(
                    config('whatsapp_gateway.username'),
                    config('whatsapp_gateway.password')
                )
                ->withHeaders([
                    'X-Device-Id' => config('whatsapp_gateway.device_id'),
                ])
                ->timeout(config('whatsapp_gateway.timeout', 10))
                ->post(config('whatsapp_gateway.base_url') . '/send/message', [
                    'phone'           => $nomorTujuan,
                    'message'         => $pesan,
                    'reply_message_id'=> '',
                    'is_forwarded'    => false,
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp Gateway: gagal mengirim pesan.', [
                    'nomor'  => $nomorTujuan,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp Gateway: exception saat mengirim pesan.', [
                'nomor'   => $nomorTujuan,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
```

### Integrasi ke `KirimKlaimPembayaranService`

Panggil `WhatsAppGatewayService` setelah status berubah menjadi `menunggu_verifikasi`:

```php
$nomorAdmin = config('whatsapp_gateway.admin_wa');

if ($nomorAdmin) {
    $pesan = $this->buatPesanKlaimAdmin($setoran);
    app(WhatsAppGatewayService::class)->kirimPesan($nomorAdmin, $pesan);
}
```

Pengiriman WhatsApp tidak boleh memblokir proses utama. Gunakan salah satu pendekatan:

- Kirim di dalam queue job terpisah (lebih disarankan).
- Atau kirim setelah commit database selesai, dengan `try/catch` yang tidak mempropagasi exception ke anggota.

Jika pengiriman gagal, proses konfirmasi anggota tetap dianggap berhasil. Catat kegagalan ke log.

### Aturan Tambahan

- Format nomor tujuan harus dalam JID: `628xxxxxxxxxx@s.whatsapp.net`. Hilangkan karakter `+`, spasi, atau tanda baca lain dari nomor telepon sebelum dikirim.
- Jika `WA_ADMIN_NOTIF_NUMBER` kosong, lewati pengiriman tanpa error.
- Jangan mengirim ulang notifikasi yang sama jika klaim sudah dalam status `menunggu_verifikasi` atau lebih lanjut.
- Notifikasi WhatsApp ini bersifat pelengkap — bukan pengganti mekanisme notifikasi in-app atau email.

---

## 16. Dashboard Admin

Filter minimal:

```text
Semua
Menunggu verifikasi
Sedang diperiksa
Perlu revisi
Disetujui
Selesai
Ditolak
Kedaluwarsa
Terlambat dikonfirmasi
Posting gagal
```

Urutan default:

```text
menunggu_verifikasi paling lama terlebih dahulu
```

Kolom tabel:

```text
Nomor setoran
Nama anggota
Nomor rekening tabungan
Nominal
Kode unik
Total pembayaran
Waktu klaim
Status
Admin pemeriksa
Usia pengajuan
```

Detail harus menampilkan:

```text
Data anggota
Data rekening tabungan
Data transaksi setoran
QRIS (gambar)
Bukti pembayaran
Data klaim pengguna
Data hasil verifikasi provider
Riwayat status
Transaksi tabungan (setelah selesai)
Catatan admin
```

---

## 17. Dual Approval untuk Nominal Besar

Opsional untuk keamanan tambahan.

Contoh aturan:

```text
Nominal <= Rp1.000.000:
    satu admin dapat menyetujui dan memposting

Nominal > Rp1.000.000:
    admin pertama memverifikasi
    admin kedua menyetujui posting
```

Jika diterapkan, tambahkan:

```text
diverifikasi_oleh    ← FK ke admins.id
diverifikasi_at
disetujui_oleh       ← FK ke admins.id (berbeda dari diverifikasi_oleh)
disetujui_at
```

Admin yang memverifikasi tidak boleh menjadi admin yang menyetujui transaksi yang sama.

---

## 18. Konfigurasi

Tambahkan file `config/setoran.php` dan daftarkan env vars di `.env.example`:

```php
return [
    'durasi_qris'          => env('SETORAN_DURASI_QRIS_MENIT', 30),
    'minimal_jumlah'       => env('SETORAN_MINIMAL', 10000),
    'maksimal_jumlah'      => env('SETORAN_MAKSIMAL', 100000000),
    'batas_transaksi_aktif'=> env('SETORAN_BATAS_AKTIF', 1),
    'nominal_dual_approval'=> env('SETORAN_DUAL_APPROVAL_NOMINAL', 1000000),
];
```

Jangan hardcode nilai-nilai di atas langsung di kode — selalu ambil dari config.

---

## 19. Acceptance Criteria

Implementasi dianggap selesai jika seluruh kondisi berikut terpenuhi:

### Pembuatan transaksi

- [ ] Anggota dapat memilih rekening tabungan miliknya (divalidasi via `users → profiles → tabungans`).
- [ ] Nominal divalidasi di backend.
- [ ] Nomor setoran unik.
- [ ] Kode unik dibuat di backend.
- [ ] `jumlah_bayar` tidak bentrok dengan transaksi aktif.
- [ ] Record dibuat sebelum QRIS.
- [ ] Dynamic QRIS menggunakan `jumlah_bayar` via infrastruktur QRIS yang sudah ada (`QrisStatic` + `buildDynamicQris`).
- [ ] Masa berlaku QRIS menggunakan nilai dari `config('setoran.durasi_qris')`.

### Konfirmasi pengguna

- [ ] Pengguna dapat menekan **Saya Sudah Membayar**.
- [ ] Bukti pembayaran dapat diunggah.
- [ ] Status berubah menjadi `menunggu_verifikasi`.
- [ ] Konfirmasi tidak menambah saldo.
- [ ] Konfirmasi terlambat diberi flag `is_terlambat`.
- [ ] Pengguna tidak dapat mengubah nominal.

### Verifikasi admin

- [ ] Admin dapat memulai review.
- [ ] Admin dapat menyetujui.
- [ ] Admin dapat menolak dengan alasan.
- [ ] Admin dapat meminta revisi bukti.
- [ ] Referensi transaksi provider dapat dicatat.
- [ ] Satu referensi provider tidak dapat digunakan dua kali.

### Posting saldo

- [ ] Posting membuat record `TransaksiTabungan` dengan `jenis_transaksi = 'debit'`.
- [ ] Posting menggunakan database transaction.
- [ ] `SetoranTabungan` dan `Tabungan` menggunakan row lock saat posting.
- [ ] Satu setoran menghasilkan satu `TransaksiTabungan` (unique constraint pada `setoran_id`).
- [ ] Saldo yang dikreditkan menggunakan `jumlah`, bukan `jumlah_bayar`.
- [ ] Status menjadi `selesai` setelah transaksi berhasil.
- [ ] Jika posting gagal, status tetap `disetujui`.
- [ ] Retry tidak membuat transaksi ganda.

### Audit dan keamanan

- [ ] Semua perubahan status memiliki record di `riwayat_status_setorans`.
- [ ] Bukti pembayaran disimpan di disk `private`.
- [ ] Gambar QRIS disimpan di disk `public` dengan kontrol akses via controller.
- [ ] Policy diterapkan.
- [ ] Endpoint memakai rate limit.
- [ ] Aksi admin mencatat identitas pelaku.
- [ ] Transaksi `selesai` tidak dapat diedit sembarangan.

---

## 20. Test Cases Wajib

### Unit test

```text
Generate kode unik
Perhitungan jumlah_bayar
Validasi transisi status
Pencegahan posting ganda
Scheduler kedaluwarsa tidak mengubah status transaksi yang sudah diklaim
```

### Feature test anggota

```text
Anggota dapat membuat setoran
Anggota tidak dapat memilih rekening anggota lain
Anggota tidak dapat mengubah nominal setelah QRIS dibuat
Anggota dapat mengirim klaim pembayaran
Anggota tidak dapat mengirim klaim dua kali
Anggota dapat memperbarui bukti saat perlu_revisi
```

### Feature test admin

```text
Admin dapat mengambil review
Admin dapat menyetujui transaksi
Admin dapat menolak dengan alasan
Admin dapat meminta revisi
Admin tidak dapat memposting transaksi yang belum disetujui
Admin tidak dapat membuat TransaksiTabungan ganda untuk setoran yang sama
```

### Concurrency test

Simulasikan dua request admin menyetujui transaksi yang sama secara bersamaan.

Hasil yang diharapkan:

```text
satu TransaksiTabungan tercipta
Status akhir selesai
```

---

## 21. Ringkasan Workflow Final

```text
1. Anggota login (guard web, App\Models\User).
2. Anggota memilih rekening tabungan miliknya (divalidasi via users → profiles → tabungans).
3. Anggota memasukkan nominal setoran.
4. Backend memvalidasi rekening, anggota, dan nominal.
5. Backend memeriksa transaksi aktif.
6. Backend membuat nomor setoran.
7. Backend membuat kode unik.
8. Backend menghitung jumlah_bayar.
9. Backend membuat record SetoranTabungan berstatus menunggu_pembayaran.
10. Backend menghasilkan Dynamic QRIS via QrisStatic + buildDynamicQris dengan nilai jumlah_bayar.
11. Anggota melakukan pembayaran menggunakan QRIS.
12. Anggota menekan tombol Saya Sudah Membayar.
13. Anggota mengunggah bukti dan data pembayaran.
14. Status berubah menjadi menunggu_verifikasi.
15. Admin membuka pengajuan (panel /admin, App\Models\Admin).
16. Status berubah menjadi sedang_diperiksa.
17. Admin mencocokkan dengan dashboard provider QRIS.
18. Jika data kurang, status menjadi perlu_revisi.
19. Jika tidak valid, status menjadi ditolak.
20. Jika valid, status menjadi disetujui.
21. Sistem membuat record TransaksiTabungan (jenis_transaksi = debit) secara atomic.
22. Status menjadi selesai.
23. Seluruh perubahan dicatat dalam riwayat_status_setorans.
```

---

## 22. Instruksi untuk AI Coding Agent

Saat mengimplementasikan spesifikasi ini:

1. Gunakan Laravel conventions.
2. Gunakan nama bahasa Indonesia untuk model baru (konsisten dengan codebase: `Tabungan`, `TransaksiTabungan`, `Deposito`, dll.).
3. Gunakan service classes untuk logika bisnis.
4. Gunakan Form Request untuk validasi.
5. Gunakan Policy untuk authorization.
6. Gunakan Enum (`StatusSetoran`) untuk status.
7. Gunakan database transaction untuk proses kritis.
8. Gunakan `lockForUpdate()` saat posting saldo.
9. Gunakan unique constraint sebagai perlindungan terakhir.
10. Gunakan infrastruktur QRIS yang sudah ada: `App\Models\QrisStatic`, logika `buildDynamicQris` di `PaymentController`, dan library `endroid/qr-code`.
11. Relasi anggota ke rekening harus melalui: `users.id → profiles.id_user → profiles.id → tabungans.id_profile`.
12. Saldo anggota dihitung dari akumulasi `TransaksiTabungan`, bukan dari kolom cached. Jangan menambah kolom `current_balance` ke `tabungans`.
13. `TransaksiTabungan::JENIS_SETORAN = 'debit'` (sudah didefinisikan di model).
14. Jangan menambah saldo dari controller konfirmasi pengguna.
15. Jangan menganggap bukti upload sebagai bukti final.
16. Jangan mengubah transaksi `selesai` secara langsung.
17. Buat migration, model, enum, service, policy, controller, route, notification, scheduler, config, dan test.
18. Ikuti acceptance criteria dan test cases pada dokumen ini.
19. Bila ada detail yang belum ditentukan, jangan mengarang aturan finansial. Tandai sebagai konfigurasi atau minta keputusan bisnis.
