---
trigger: always_on
---

---
inclusion: always
---

# Product Domain: Dash-Kospin

Indonesian cooperative savings and loan (KoSPIN) management system.

## Domain Terminology

Always use Indonesian terms for domain concepts:

- **Tabungan** - Savings accounts
- **Pinjaman** - Loans
- **Pelunasan** - Loan repayments
- **Deposito** - Fixed deposits
- **Nasabah/Anggota** - Members/customers
- **Transaksi** - Transactions

## Core Modules

### Savings (Tabungan)
- Multiple product types via `ProdukTabungan`
- Transaction tracking via `TransaksiTabungan`
- Barcode/QR scanning for quick lookup
- Automatic interest calculation via observers

### Loans (Pinjaman)
Three product types:
- **Gadai** - Pawn loans
- **Kredit Elektronik** - Electronic credit
- **Cicilan Emas** - Gold installment

### Deposits (Deposito)
- Fixed-term deposits with auto-interest
- Maturity tracking and notifications

### Referral System
- Member referral tracking (`AnggotaReferral`)
- Commission management (`TransaksiReferral`)

### Makan Bergizi Gratis (MBG)
- Free nutritious meal program
- Public checkout with QR codes
- Hashids-based secure URLs (never expose DB IDs)
- Daily quota management

## Business Rules

### Financial Data
- **Storage:** All monetary values as integers (smallest unit)
- **Display:** Use `format_rupiah()` for currency (Rp 1.000.000)
- **Words:** Use `terbilang()` for number-to-words conversion
- **Format:** 1.000.000,00 (period thousands, comma decimals)

### Security
- **Public IDs:** Always use Hashids, never expose database IDs
- **Barcode Scanning:** Requires authentication (Sanctum tokens)
- **Activity Logging:** Log all financial transactions via `BarcodeScanLog`
- **Rate Limiting:** Apply to scan endpoints

### Reporting
- **PDF Generation:** Use `PdfHelper` class with DOMPDF
- **Bulk Exports:** Support large datasets with progress tracking
- **Storage:** `storage/app/public/reports/` for public, `storage/app/temp/` for private
- **Cleanup:** Auto-delete old reports via scheduled command

## API Conventions

### Responses
- Use API Resources (`TabunganScanResource`, `MakanBergizisGratisResource`)
- Eager load relationships to prevent N+1
- Return proper HTTP status codes (200, 201, 404, 422, 500)

### Barcode/QR Integration
- Webhook support for external scanners
- Scan logging via `BarcodeScanLog` model
- Endpoints: `/api/barcode/scan`, `/api/tabungan/scan/{barcode}`

## User Interface

### Admin Panel (Filament)
- Primary staff interface
- Role-based access control (Spatie Permission)
- Custom pages for reports (`LaporanTabungan`, `LaporanGadai`, etc.)
- Dashboard widgets for statistics

### Public Pages
- Minimal/no authentication for public features
- Mobile-responsive (TailwindCSS)
- QR scanning interfaces (`/tabungan/scan/{barcode}`)
- MBG checkout (`/makan-bergizi-gratis/checkout/{hashid}`)

## Localization

- **Language:** Indonesian (Bahasa Indonesia)
- **Date:** DD/MM/YYYY or Indonesian long format
- **Currency:** Indonesian Rupiah (IDR)
- **Numbers:** 1.000.000,00 format
