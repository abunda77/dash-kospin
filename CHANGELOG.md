# Changelog

Semua perubahan penting pada proyek ini akan dicatat di file ini.

Format berdasarkan [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [2026-08-08]

### Added
- Metode pembayaran `Transfer Rekening` pada Setoran Simpanan sebagai alternatif QRIS, dengan rekening tujuan BCA, nomor rekening `0889333288`, atas nama `KOPERASI SINARA ARTHA`
- Pilihan metode pembayaran pada halaman user `/user/setoran-simpanan`, beserta tampilan instruksi transfer, total pembayaran termasuk kode unik, dan form konfirmasi pembayaran
- Penyimpanan metode pembayaran setoran melalui enum `MetodePembayaranSetoran` dan kolom `metode_pembayaran` pada tabel `setoran_tabungans`
- Informasi dan filter metode pembayaran pada resource Setoran Simpanan di dashboard admin
- Dukungan `transfer_rekening` pada `POST /api/setoran` serta field response `metode_pembayaran`, `metode_pembayaran_label`, dan `rekening_transfer` untuk aplikasi mobile
- Section khusus update Transfer Rekening, kontrak TypeScript, contoh response, dan panduan implementasi React Native pada `SIMPANAN-SETORAN-API.md`
- Feature test halaman user dan API untuk alur Transfer Rekening tanpa ketergantungan pada QRIS statis

### Changed
- Notifikasi klaim setoran pada dashboard admin dan WhatsApp kini menyertakan metode pembayaran dan berlaku untuk QRIS maupun Transfer Rekening
- Deskripsi transaksi tabungan yang diposting kini mengikuti metode pembayaran setoran
- Istilah dan flow Setoran Simpanan digeneralisasi dari QRIS-only menjadi QRIS / Transfer Rekening
- Kontrak API tetap kompatibel dengan aplikasi lama: request tanpa `metode_pembayaran` menggunakan `qris`

---

## [2026-08-07]

### Added
- API endpoint Penarikan Simpanan untuk mobile: `GET /api/penarikan/rekening-options`, `POST /api/penarikan`, `GET /api/penarikan/aktif`, `GET /api/penarikan/history`, `GET /api/penarikan/{id}`, `POST /api/penarikan/{id}/revisi`, `POST /api/penarikan/{id}/batalkan`
- API endpoint Setoran Simpanan (QRIS) untuk mobile: `GET /api/setoran/rekening-options`, `POST /api/setoran` (generate QRIS), `GET /api/setoran/aktif`, `GET /api/setoran/history`, `GET /api/setoran/{id}`, `POST /api/setoran/{id}/klaim`, `POST /api/setoran/{id}/batalkan`
- Controller API `PenarikanSimpananController` dan `SetoranSimpananController` (auth Sanctum, validasi kepemilikan rekening & transaksi, menggunakan service workflow yang sama dengan halaman Filament user)
- Form Request: `StorePenarikanRequest`, `KirimRevisiPenarikanRequest`, `StoreSetoranRequest`, `KlaimSetoranRequest` dengan pesan validasi Bahasa Indonesia
- Fungsi Batalkan Setoran dan Batalkan Penarikan pada dashboard user, lengkap dengan konfirmasi, otorisasi kepemilikan, pencatatan riwayat status, dan pembaruan otomatis pada tab dashboard admin
- Feature test API dan halaman Filament user untuk pembatalan Setoran Simpanan dan Penarikan Simpanan
- File dokumentasi `SIMPANAN-SETORAN-API.md`: panduan integrasi untuk aplikasi mobile React Native

---

## [2026-08-06]

### Added
- Fitur Penarikan Simpanan (analogi Setoran Simpanan, tanpa QRIS dinamis)
- Halaman user `/user/penarikan-simpanan`: form pengajuan dengan kolom Bank, Nama Bank, dan Nama Nasabah; nominal preset/kustom; referensi, bukti pendukung, dan catatan; panel status, form revisi, dan riwayat penarikan
- Resource admin `/admin/penarikan-tabungans`: tabel dengan tabs (Perlu Tindakan, Selesai, Ditolak/Batal), filter, infolist detail, dan aksi Mulai Review, Minta Revisi, Tolak, Setujui, Coba Ulang Posting
- Migrasi: tabel `penarikan_tabungans`, `riwayat_status_penarikans`, `bukti_penarikans`, dan kolom `penarikan_id` pada `transaksi_tabungans`
- Enum `StatusPenarikan` (menunggu_verifikasi s.d. dibatalkan)
- Service workflow: `BuatPenarikanTabungan`, `KirimRevisiPenarikan`, `MulaiReviewPenarikan`, `MintaRevisiPenarikan`, `SetujuiPenarikan`, `TolakPenarikan`, `PostingPenarikanKeTabungan`, `CatatRiwayatStatusPenarikan`
- Job `SendPenarikanNotificationJob` (notifikasi WhatsApp admin) + notifikasi database Filament
- Policy `PenarikanTabunganPolicy` terdaftar di `AppServiceProvider`
- Config `config/penarikan.php` dengan env `PENARIKAN_MINIMAL`, `PENARIKAN_MAKSIMAL`, `PENARIKAN_BATAS_AKTIF`
- Feature test `PenarikanSimpananWorkflowTest` (5 skenario: workflow lengkap, penolakan, limit saldo, limit transaksi aktif, coba ulang posting)

### Changed
- Model `TransaksiTabungan`: tambah `penarikan_id` pada `$fillable` dan relasi `penarikan()`; penarikan yang disetujui diposting sebagai transaksi `kredit` (mengurangi saldo)

---

## [2026-08-05]

### Fixed
- Form karyawan: kolom `nama` dan `no_telepon_keluarga` tidak valid pada tabel `karyawans`
- Model Karyawan: `nama` → `first_name` + `last_name`; hapus `no_telepon_keluarga` dari `$fillable`
- Tambah accessor `nama` untuk backward compatibility
- Update form, table, infolist KaryawanResource: ganti `nama` → `first_name` + `last_name`, hapus field `no_telepon_keluarga`
- Update Job SendWhatsAppMessage dan SendBulkWhatsAppMessage: tambah variabel `{first_name}`, `{last_name}`
- Update PDF view karyawan dan KirimWAKaryawan: sesuaikan dengan field baru

### Changed
- Migrasi `create_karyawans_table`: `nama` → `first_name` + `last_name`; hapus `no_telepon_keluarga`

---

## [2026-08-02]

### Added
- Custom halaman autentikasi: Login, Register, dan Lupa Password (/login, /register, /forgot-password)
- Livewire komponen: ModernLogin, ModernRegister, ModernForgotPassword
- Halaman autentikasi dengan branding Kospin, dark mode, dan validasi Bahasa Indonesia
- Payment API group: endpoint QRIS statis, detail, validasi, dan generate dinamis
- File dokumentasi PaymentQR.md: workflow dan referensi endpoint Payment API

---

## [2026-07-31]

### Added
- Note column pada model Tabungan

---

## [2026-07-30]

### Fixed
- Password update

---

## [2026-07-19]

### Changed
- TTD kontrak deposito

---

## [2026-07-17]

### Changed
- Revisi kolom nama Produk Simpanan pada Sertifikat

---

## [2026-07-16]

### Added
- Sertifikat simpanan

---

## [2026-07-15]

### Changed
- Update AGENTS.md, CLAUDE.md, GEMINI.md

---

## [2026-07-06]

### Changed
- Update CLAUDE.md

---

## [2026-06-27]

### Changed
- Background kartu member

---

## [2026-06-26]

### Changed
- Kontrak: Surabaya 2 Denpasar

---

## [2026-06-24]

### Changed
- Kontrak sertifikat deposito ke Denpasar

---

## [2026-06-23]

### Changed
- Kontrak deposito Surabaya 2 Denpasar

---

## [2026-06-14]

### Changed
- Kontrak simpanan kerta
- Nama produk simpanan

---

## [2026-06-13]

### Changed
- Ukuran kartu member

---

## [2026-06-04]

### Added
- Simpanan sinaran kontrak

### Changed
- Info simpanan sinaran

---

## [2026-06-03]

### Changed
- Logo | Security composer npm

---

## [2026-04-06]

### Fixed
- Tabungan update status kode teller
- Status rekening tabungan: aktif/ditutup

### Changed
- TTD kontrak deposito a.n Jimmy Tandiono

---

## [2026-01-07]

### Fixed
- Widget tabungan

---

## [2025-12-16]

### Fixed
- Tabungan user_id -> id (final)

---

## [2025-12-15]

### Added
- Command script fix profile tabungan

### Fixed
- Command script fix tabungan id -> id_user
- Relasi model Tabungan ke profile
- Bugs tabungan pinjaman no kontrak

### Reverted
- "fix: relationmeleset tabungan dan pinjaman"
- "perbaikan relation pada widget User panel"
- "fix: bugs tabungan pinjaman no kontrak"

---

## [2025-12-10]

### Fixed
- Relation meleset tabungan dan pinjaman
- Perbaikan relation pada widget User panel

---

## [2025-12-09]

### Added
- Laravel Boost
- Request mobile apps by email

### Changed
- Modifikasi UI mobile app request

---

## [2025-12-08]

### Added
- Page panel user: tabungan, pinjaman, deposito
- Reset password untuk admin panel

---

## [2025-12-03]

### Added
- Struk MBS
- Struk MBS 2

---

## [2025-11-26]

### Added
- Agent rule antigravity

### Changed
- Setoran awal menjadi 10%

---

## [2025-11-10]

### Changed
- Perhitungan cicilan emas

---

## [2025-11-07]

### Changed
- Alamat logo

---

## [2025-10-27]

### Added
- Route QRIS generator + fix button
- Feature QRIS generator + migrasi + composer

---

## [2025-10-25]

### Fixed
- Form kirim WA karyawan

---

## [2025-10-24]

### Changed
- Resource karyawan lebih simple
- Hapus require asterik data karyawan

---

## [2025-10-20]

### Fixed
- File migrasi produk tabungan table

---

## [2025-10-11]

### Added
- MBG scan barcode /api & checkout

---

## [2025-10-09]

### Added
- Webhook external barcode tabungan
- Tanggal transaksi terakhir scan barcode

---

## [2025-10-08]

### Added
- Barcode tabungan
- Fitur barcode tabungan lengkap

### Fixed
- JSON barcode output
- Hash URL scan barcode tabungan
- Code barcode tabungan
- Command clear log barcode

### Changed
- Warna card warna nomor tabungan

---

## [2025-10-04]

### Added
- Kontrak tabungan sinara mitra

---

## [2025-09-29]

### Added
- Laporan Nasabah Aktif

---

## [2025-08-12]

### Fixed
- Color table deposito

---

## [2025-08-11]

### Added
- Laporan emas, gadai, cicilan elektronik
- Laporan deposito + command
- Saving command

### Fixed
- Saving export to blade reports

### Changed
- Gitignore: tambah line ./kiro

---

## [2025-08-10]

### Added
- Command export loan

### Fixed
- Layout laporan transaksi
- Chunk load report export

---

## [2025-08-09]

### Fixed
- Link WA & laporan pinjaman
- Chunk load report export (multiple fixes)

---

## [2025-08-07]

### Changed
- Simulasi kredit untuk bulanan

---

## [2025-08-06]

### Added
- Policy for page
- Log saving report
- Laporan Tabungan

### Fixed
- Export PDF laporan pinjaman

### Changed
- Patch: no WA kantor
- Web route & PDF helper
- Halaman laporan pinjaman for export dan loading

---

## [2025-08-05]

### Added
- Halaman NPL
- Halaman Laporan Keterlambatan 90 hari & Laporan Pinjaman

---

## [2025-07-30]

### Changed
- Ganti andesta rully to andreas widea
- Update API WhatsApp menggunakan WAHA

### Fixed
- Bugs send bulk WhatsApp
- Bugs send via WAHA
- Class textarea

---

## [2025-07-18]

### Added
- Observer: tabungan, pinjaman, deposito, trx pinjaman & tabungan

---

## [2025-07-08]

### Changed
- Form pengajuan kredit + status rumah dan lama

---

## [2025-06-24]

### Changed
- Formulir pengajuan pinjaman - biaya administrasi 50 rb

---

## [2025-06-17]

### Changed
- Form pengajuan pinjaman - tambah row bersedia transfer

---

## [2025-06-10]

### Added
- Pelunasan Resource, Pelunasan Model, surat pelunasan

### Changed
- Redirect after create PELUNASAN
- Update README

---

## [2025-06-08]

### Changed
- Gitignore

---

## [2025-05-27]

### Added
- CLAUDE.md

---

## [2025-05-26]

### Changed
- Form pengajuan kredit add brand usaha

---

## [2025-05-23]

### Changed
- Sertifikat deposito geser table rekening detail

---

## [2025-05-22]

### Changed
- Mutasi angsuran jangka_waktu_satuan

---

## [2025-05-17]

### Changed
- Sertifikat deposito menjadi A5

### Added
- File md CLI 11 file

---

## [2025-05-14]

### Changed
- README.md + add composer.md

---

## [2025-05-13]

### Changed
- Sertifikat deposito ornament border + project rules

---

## [2025-05-12]

### Changed
- README.md

---

## [2025-04-23]

### Changed
- Kolom mutasi tabungan name teller

---

## [2025-04-22]

### Changed
- Form pengajuan opsi cash/transfer

---

## [2025-04-21]

### Changed
- Kolom mutasi kredit debit pada mutasi cetak

---

## [2025-04-16]

### Changed
- Table harga emas

---

## [2025-04-14]

### Changed
- Cicilan emas 1-6 bulan

---

## [2025-04-12]

### Changed
- Simulasi pinjaman
- Kontrak pinjaman tanpa hold untuk pinjaman instant

---

## [2025-04-09]

### Changed
- Jangka waktu satuan minggu

---

## [2025-04-07]

### Changed
- Bunga per tahun up 500%

---

## [2025-03-20]

### Changed
- Cicilan emas: add 3 gram

---

## [2025-03-17]

### Changed
- Kontrak deposito: hapus 0,5%

---

## [2025-03-15]

### Fixed
- Simulasi bunga 5%

---

## [2025-03-14]

### Changed
- Kontrak umroh, cicilan emas, dan harga emas

---

## [2025-03-13]

### Changed
- Kontrak emas, travelling, dan lebaran

---

## [2025-03-06]

### Added
- Kontrak liburan

### Changed
- Kontrak cicilan emas, hari raya

---

## [2025-03-05]

### Changed
- PDF kontrak emas, tabungan lebaran, umroh

### Fixed
- Error notif class

---

## [2025-03-02]

### Added
- Cicilan Emas Resource

---

## [2025-03-01]

### Added
- Tabungan lebaran

### Changed
- Kontrak tabungan lebaran
- Rate cicilan emas

---

## [2025-02-28]

### Changed
- PDF redaksional harga emas
- Tabungan umroh syarat & ketentuan
- Simpanan pokok 100k menjadi 50k

### Added
- List daftar harga emas dari Toped dan cetak PDF

---

## [2025-02-27]

### Added
- Kontrak tabungan umroh

---

## [2025-02-25]

### Added
- Controller API app mobile url

---

## [2025-02-24]

### Added
- API mutasi tabungan by periode

### Changed
- Kontrak kredit elektronik 1 bln angsuran

### Fixed
- Route API mutasi tabungan

---

## [2025-02-21]

### Added
- Gadai dan kredit elektronik V1
- Fitur Gadai beserta kontrak

---

## [2025-02-17]

### Added
- Button lunas di page TableAngsuran
- Route & create post angsuran API & update page TableAngsuran

### Fixed
- Doc scramble protection password

---

## [2025-02-15]

### Added
- AngsuranController API
- API region controller

### Changed
- API region 2

---

## [2025-02-13]

### Changed
- Kontrak pinjaman PDF
- Kontrak deposito
- Sertifikat
- Form pengajuan kredit

---

## [2025-02-10]

### Added
- API banner mobile

### Changed
- Form kontrak perjanjian kredit

---

## [2025-02-03]

### Changed
- Form pengajuan kredit (checkbox & revisi)

---

## [2025-02-02]

### Changed
- Form pengajuan kredit

---

## [2025-02-01]

### Changed
- Form pengajuan kredit

---

## [2025-01-30]

### Added
- Form pengajuan kredit

---

## [2025-01-26]

### Fixed
- Daftar nasabah terlambat

---

## [2025-01-25]

### Added
- Scramble docs

---

## [2025-01-23]

### Fixed
- Surat kontrak pinjaman

---

## [2025-01-22]

### Added
- Catatan kredit dan referral

### Changed
- Kontrak pinjaman

### Fixed
- Table layout

---

## [2025-01-21]

### Changed
- TTD pihak pertama
- Ylius Christian

### Fixed
- Bunga dan angsuran pinjaman

---

## [2025-01-18]

### Changed
- Slip setoran awal tabungan (layout margin)

---

## [2025-01-15]

### Added
- Cetak slip setoran awal

### Changed
- Editable rekening deposito dan pinjaman
- Rekening pinjaman
- Mutasi range

---

## [2025-01-14]

### Changed
- Lebar kolom cetak buku tabungan
- Editable no rekening

### Fixed
- Simulasi kredit PDF

---

## [2025-01-13]

### Added
- Simulasi kredit

---

## [2025-01-09]

### Changed
- Cetak mutasi buku: cetak baris ke row

---

## [2025-01-08]

### Changed
- Job API WhatsApp env

---

## [2025-01-05]

### Added
- Data rekening bank ke deposito rekening

---

## [2025-01-04]

### Added
- Page pencairan deposito
- Jadwal jatuh tempo deposito tahun ini

### Changed
- Console backup database tiap 6 jam
- Config log file

---

## [2025-01-02]

### Added
- Kirim WhatsApp karyawan personal dan massal
- Kirim bulk mail

### Changed
- Variabel first_name last_name modul kirim email

### Fixed
- Perhitungan denda bulan ini
- Perhitungan total bayar angsuran

---

## [2025-01-01]

### Added
- Data karyawan resource

---

## [2024-12-31]

### Added
- Welcome page dengan quotes
- Shadcn UI (installasi)

---

## [2024-12-30]

### Added
- Schedule backup:run
- Welcome page dengan quotes

### Changed
- Config dump
- Welcome blade

---

## [2024-12-29]

### Added
- Event listener deposito

### Changed
- Database config
- File PENTING (deleted)

---

## [2024-12-28]

### Added
- Event/Listener: Transaksi Pinjaman/Tabungan

### Fixed
- Pinjaman webhook
- Pinjaman tabungan webhook + merge transaksi

---

## [2024-12-27]

### Fixed
- Halaman reset password

---

## [2024-12-26]

### Added
- Route API forgot password & reset password
- Job queue forgot password
- Logo email template
- Plugin edit env
- Clear cache button

### Changed
- Form dan surat PDF
- Mail.php

### Fixed
- Logo ilang (multiple fixes)
- Route reset password
- Komentar di blade mutasi tabungan v2

---

## [2024-12-25]

### Added
- Surat kontrak pinjaman, deposito, tabungan
- Class URL for SSL AppServiceProvider
- Speedtest pages

### Changed
- Fitur penggabungan transaksi
- Hari dan penyebutan nominal

### Fixed
- Route web

---

## [2024-12-24]

### Changed
- Button spin animasi
- Mutasi V2 + custom date periode

---

## [2024-12-23]

### Added
- Cetak PDF profile + ibukandung table

---

## [2024-12-22]

### Added
- Session empty button dan model

### Changed
- List keterlambatan bulan ini + count label navigation
- List keterlambatan bulan ini dan bulan lalu
- TableAngsuran ubah asc to desc
- TableAngsuran new line
- Mutasi tabungan total kredit, debit, saldo akhir
- Avatar profile

### Fixed
- Console command untuk hitung bunga add schedule
- Perhitungan saldo akhir
- Total kredit debit mutasi V2

---

## [2024-12-21]

### Added
- Reminder Job
- Link nav & add artisan queue:worker to plugin

### Changed
- Layout form kirim email
- Label install octane

---

## [2024-12-20]

### Added
- Model, migration, dan resource birthday greetings
- Form email
- Job Queue

### Changed
- Page send birthday greeting in widget dan page livewire

---

## [2024-12-19]

### Added
- Widget birthday
- Redis
- Model migration and resource birthday greetings

### Fixed
- Seeder, profile factory
- Error match case

---

## [2024-12-18]

### Added
- Seeder user factory
- Birthday fitur
- Birthday logs

### Changed
- Rename label navigation
- Realtime table history log

---

## [2024-12-17]

### Added
- Mutasi tabungan versi 2
- Bulk edit saldo tabungan

### Fixed
- Header image pada PDF
- Image column profile

### Changed
- PDF style table

---

## [2024-12-16]

### Added
- Monitor schedule task
- Cek saldo tabungan + list saldo
- Model Image Resource

### Changed
- Saldo tabungan
- Image logo

---

## [2024-12-15]

### Added
- Reminder page
- Filament health

### Fixed
- Keterlambatan
- Empty data activity log

---

## [2024-12-14]

### Changed
- Policy activity dan artisan

### Added
- Activity log plugin

---

## [2024-12-13]

### Added
- Button delete activity log
- Data Table
- Artisan page

---

## [2024-12-12]

### Added
- Test

---

## [2024-12-11]

### Added
- Filament logger
- File PENTING.txt

### Fixed
- Rename dan reorder menu

---

## [2024-12-10]

### Added
- Plugin activity
- Cetak invoice angsuran

### Fixed
- Role dan permission
- Debug dan refactor

---

## [2024-12-09]

### Added
- API route update password

### Fixed
- Uniq number dan fix prompt hitung tabungan
- Activity log (not fix)

---

## [2024-12-08]

### Fixed
- Mutasi PDF data filter
- Session dan cache driver

---

## [2024-12-07]

### Changed
- Perhitungan bunga tabungan command

---

## [2024-12-06]

### Added
- Deposito ver 1.0
- Deposito API

### Fixed
- Error handling API

---

## [2024-12-05]

### Added
- Fitur backup database
- Fitur empty data
- Cetak PDF list keterlambatan

### Changed
- Fitur Download

---

## [2024-12-04]

### Added
- API routers pinjaman, tabungan, profile

---

## [2024-12-03]

### Added
- Fitur cetak PDF tabel mutasi tabungan
- Penambahan fitur image profile pada table pinjaman

### Fixed
- PDF ver 1.0

---

## [2024-12-02]

### Fixed
- Tabungan dan pinjaman mutasi

### Changed
- History pembayaran dan tombol payment

---

## [2024-12-01]

### Added
- Modul pinjaman

---

## [2024-11-30]

### Fixed
- Mutasi tabungan ver 1.0
- Mutasi ver 2.0 + debugbar

---

## [2024-11-28]

### Added
- Transaksi tabungan

### Changed
- Produk jenis tabungan

---

## [2024-11-08]

### Fixed
- Bug: first_name

---

## [2024-11-07]

### Added
- Route API login register logout

---

## [2024-11-06]

### Fixed
- User login role

---

## [2024-11-05]

### Added
- Role
- Resource Admin
- User panel

### Fixed
- Userpanel registration

---

## [2024-10-28]

### Added
- Filament
- Shield
- API Service
- Panel user

### Changed
- Panel login()

---

## [2024-10-21]

### Added
- First commit (inisialisasi proyek)

### Changed
- Gitignore
