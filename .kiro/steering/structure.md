---
inclusion: always
---

# Project Structure & Architecture

## Directory Organization

```
app/
├── Console/Commands/     # Artisan commands (exports, cleanup, calculations)
├── Filament/            # Admin panel (Resources, Pages, Widgets)
├── Helpers/             # Helper classes (PdfHelper, HashidsHelper)
├── Http/                # Controllers, Middleware, Resources (API)
├── Jobs/                # Queue jobs for background tasks
├── Livewire/            # Standalone Livewire components
├── Models/              # Eloquent models with relationships
├── Observers/           # Model lifecycle hooks
├── Policies/            # Authorization logic
├── Services/            # Business logic layer
└── helpers.php          # Global helper functions

resources/views/
├── filament/            # Filament custom views
├── livewire/            # Livewire component views
├── reports/             # PDF templates (DOMPDF)
└── tabungan/            # Public pages (barcode scanning)

storage/app/
├── public/reports/      # Public PDF exports
└── temp/                # Temporary export files
```

## Core Models

**Financial Accounts:**

-   `Tabungan` - Savings accounts
-   `Pinjaman` - Loan accounts
-   `Deposito` - Fixed deposits
-   `Pelunasan` - Loan repayments

**Transactions:**

-   `TransaksiTabungan` - Savings transactions
-   `TransaksiPinjaman` - Loan transactions
-   `TransaksiReferral` - Referral commissions

**Products & Members:**

-   `ProdukTabungan`, `ProdukPinjaman` - Product definitions
-   `Profile` - Member profiles
-   `AnggotaReferral` - Referral relationships

**Special Features:**

-   `MakanBergizisGratis` - Free meal program
-   `BarcodeScanLog` - Barcode/QR scan tracking

## Architectural Patterns

### Service Layer

Move complex business logic to services:

-   `SavingsReportExportService` - Report generation
-   `SavingsReportService` - Savings business logic
-   Keep controllers thin, delegate to services

### Observer Pattern

Use observers for automatic model actions:

-   `TabunganObserver` - Auto-generate account numbers
-   `TransaksiTabunganObserver` - Update balances
-   `DepositoObserver` - Calculate interest
-   Register in `AppServiceProvider::boot()`

### Command Pattern

Background operations via Artisan commands:

-   `ExportLoanReportCommand` - Async PDF exports
-   `CleanupReportFilesCommand` - Scheduled cleanup
-   `CleanupBarcodeScanLogs` - Log maintenance

### Helper Classes

Reusable utilities:

-   `PdfHelper` - Consistent PDF formatting
-   `HashidsHelper` - ID obfuscation for public URLs
-   Global helpers in `app/helpers.php`

## Naming Conventions

**Models:** PascalCase, singular, Indonesian domain terms

-   `TransaksiTabungan`, `ProdukPinjaman`

**Controllers:** PascalCase + `Controller` suffix

-   `TabunganBarcodeController`, `Api\BarcodeScanController`

**Services:** PascalCase + `Service` suffix

-   `LoanReportExportService`

**Commands:** PascalCase + `Command` suffix

-   `ExportSavingsReportCommand`

**Views:** kebab-case

-   `reports/laporan-pinjaman.blade.php`
-   `livewire/makan-bergizis-gratis-checkout.blade.php`

**Routes:** kebab-case, Indonesian for user-facing

-   `/tabungan/scan/{barcode}`
-   `/api/barcode/scan`

## Code Organization Rules

1. **Single Responsibility** - One class, one purpose
2. **Dependency Injection** - Constructor injection over facades in services
3. **Eager Loading** - Always prevent N+1 queries with `with()`
4. **Scopes** - Use query scopes for reusable filters
5. **API Resources** - Transform models consistently for API responses
6. **Form Requests** - Validate in dedicated request classes
7. **Observers** - Auto-register in `AppServiceProvider`
8. **Policies** - Authorization logic separate from controllers

## Indonesian Localization

-   Use `format_rupiah()` for currency (Rp 1.000.000)
-   Use `terbilang()` for number-to-words
-   Date format: DD/MM/YYYY or Indonesian long format
-   Number format: 1.000.000,00 (period thousands, comma decimals)
