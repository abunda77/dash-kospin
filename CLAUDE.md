# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Dash-Kospin is a Laravel 11 application for a savings and loans cooperative. Its main UI is built with Filament 3 and Livewire 3. The application manages members, savings, loans, deposits, referrals, specialized credit products, reports, barcode/QRIS features, and WhatsApp/email notifications.

The installed runtime currently uses PHP 8.3, Laravel 11, Filament 3, Livewire 3, PHPUnit 11, Pint 1, and Tailwind CSS 3. Composer permits PHP 8.2 or newer.

## Commands

### Initial setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
```

`package-lock.json` is committed, so prefer `npm ci` for a reproducible install. The example environment uses SQLite plus database-backed cache, sessions, and queues; deployments may use MySQL and Redis. Configure `.env` before migrating. Do not assume Redis or MySQL is active merely because the packages support them.

### Development

```bash
# Starts the web server, queue listener, Pail, and Vite together
composer dev

# Run services separately when needed
php artisan serve
php artisan queue:listen --tries=1
php artisan pail
npm run dev

# Clear generated application caches
php artisan optimize:clear
```

Long-running notification work depends on a queue worker. The default example configuration uses the database queue.

### Tests and formatting

```bash
# Entire suite
php artisan test

# One test file
php artisan test tests/Feature/ExampleTest.php

# One test method
php artisan test tests/Feature/ExampleTest.php --filter=test_response_time_single_request

# Format only changed PHP files before finalizing PHP changes
vendor/bin/pint --dirty
```

`phpunit.xml` does not override the database connection—the SQLite test settings are commented out—so tests inherit the active environment database unless explicitly configured otherwise. The current suite has only basic unit checks and HTTP stress/response-time tests; it has no meaningful domain coverage. There is no npm lint, type-check, or JavaScript test script.

### Scheduler and maintenance

```bash
php artisan schedule:list
php artisan schedule:work
php artisan migrate:status
php artisan route:list

php artisan backup:run --only-db
php artisan barcode:cleanup-logs --days=90
```

Production scheduling is defined in `routes/console.php`: savings interest runs monthly, database backup runs every four hours, and barcode log cleanup runs Sundays at 02:00. The monthly schedule currently uses `monthlyOn(date('t'), '23:59')`; because the day is calculated when the schedule is loaded, verify its behavior before relying on it as a true last-day-of-month schedule.

### Domain commands

```bash
# Calculate interest for every savings account, then remove duplicates
php artisan tabungan:hitung-bunga --all
php artisan tabungan:hitung-bunga --hapus-duplikat

# PDF report exports
php artisan report:export-loan --type=transaction --start-date=2024-01-01 --end-date=2024-12-31
php artisan report:export-savings --type=transaction --product=1
php artisan report:export-deposit --status=active --jangka-waktu=12
php artisan report:check-progress
php artisan report:cleanup --hours=24

# Other repair/diagnostic commands
php artisan app:fix-tabungan-profile-data --dry-run
php artisan test:pdf-generation
php artisan permission:assign-keterlambatan-90-hari
```

Run `php artisan help <command>` before using repair, cleanup, or export commands with unfamiliar options. Some commands are interactive unless an option such as `--all`, `--dry-run`, or `--force` is supplied. When running Artisan commands non-interactively (CI, automation, or agent workflows), pass `--no-interaction` where applicable.

## Architecture

### Laravel entry points

- `bootstrap/app.php` registers `routes/web.php`, `routes/api.php`, `routes/console.php`, the `/up` health route, trusted proxies, API exception rendering, and the savings-interest command. Laravel 11 has no application Console Kernel.
- `bootstrap/providers.php` registers `AppServiceProvider`, `AdminPanelProvider`, and `UserPanelProvider`.
- `AppServiceProvider` registers the financial-model observers and the API documentation gate.
- Commands under `app/Console/Commands` are auto-discovered; the interest command is also explicitly registered in `bootstrap/app.php`.

### Authentication and Filament panels

The application has separate session-authenticated Filament surfaces plus Sanctum API authentication:

- `/admin` uses the `admin` guard and `App\Models\Admin`. `AdminPanelProvider` discovers `app/Filament/Resources`, `app/Filament/Pages`, and admin widgets, and installs Shield plus operational plugins.
- `/user` uses the `web` guard and `App\Models\User`. `UserPanelProvider` discovers only `app/Filament/User/Resources`, `Pages`, and `Widgets`; registration is enabled and admin plugins are not loaded.
- Sanctum protects selected routes for `User`; the `Admin` model is not the API identity.

Flat resources in `app/Filament/Resources` therefore belong to the admin panel by default. User-facing pages and widgets are intentionally isolated under `app/Filament/User`. User pages scope queries through the authenticated user's profile rather than relying on Filament Shield.

### Domain model and key conventions

`Profile` is the center of the member domain, but historical schema choices use two identifiers. Check both migrations and relationship definitions before joining or filtering:

- `profiles` has its own `id`, while `Profile` uses `id_user` as its Eloquent primary key.
- `Tabungan.id_profile` points to `profiles.id`.
- `Pinjaman.profile_id` and `Deposito.id_user` point to `profiles.id_user`.

This distinction is reflected in the user panel: savings queries use `$profile->id`, while loan queries use `$profile->id_user`. Do not normalize these keys incidentally during unrelated work.

The main financial aggregates are:

- `Tabungan` → `ProdukTabungan` and many `TransaksiTabungan` records.
- `Pinjaman` (custom key `id_pinjaman`) → `ProdukPinjaman`, interest/penalty configuration, and many `TransaksiPinjaman` records. Gadai and electronic credit are optional one-to-one extensions; gold installments are one-to-many.
- `Deposito` calculates maturity and interest during model creation and dispatches lifecycle events.

Much of the financial domain uses Spatie activity logging. Inspect model casts, event maps, and relationship keys before changing persistence behavior.

### HTTP/API boundaries

- `routes/web.php` contains public pages outside Filament: report downloads/progress monitoring, barcode printing/scanning, the Makan Bergizi Livewire checkout, QRIS generation, password reset, and mobile-app request pages.
- `routes/api.php` mixes public mobile endpoints with a Sanctum-protected group. Authentication, regions, banners, some transaction-history/barcode/program endpoints are public; member/profile/financial actions are protected selectively.
- API controllers under `app/Http/Controllers/Api` generally return hand-built response envelopes rather than Eloquent API Resources. Follow the neighboring controller's established response shape.

Report download and export-progress routes are currently public and filename/cache-key based. Preserve their sanitization when changing report storage, and treat any authorization change as an API/operational compatibility change.

### Events, observers, and queues

Financial integration has two source-level mechanisms:

1. Observers registered by `AppServiceProvider` synchronously POST created records to a generic webhook URL and swallow/log failures.
2. Models map lifecycle events to event classes, while `App\Providers\EventServiceProvider` declares more specific webhook listeners.

`EventServiceProvider` is not currently listed in `bootstrap/providers.php`; do not assume those listener mappings are active without verifying runtime registration. Registering it could also cause created records to take both observer and listener paths.

Queued jobs in `app/Jobs` handle WhatsApp messages, reminders, profile email, and password-reset email. They are dispatched mostly from Filament pages and API controllers, not from the scheduler. Several integrations read WhatsApp/N8N environment values directly while transaction listeners use `config/services.php`, so integration configuration is split across both patterns.

### Reports and exports

There are two report paths:

- Filament report pages provide interactive filtering, statistics, and direct PDF downloads.
- `report:export-*` commands perform chunked bulk PDF exports, store progress in cache, and optionally publish files under `storage/app/public/reports`.

Loans and savings use paired query/export services in `app/Services`; deposits build their report query and PDF more directly. CLI progress keys use a short cache lifetime and are removed during command cleanup, so `/export-monitor` is a transient view rather than a durable job history. Report cleanup covers both temporary and public PDF locations.

### Background/public feature verticals

Barcode scanning, QRIS, and the Makan Bergizi program cross models, migrations, API/web controllers, Filament resources, and public pages. They do not fit entirely inside either Filament panel. When modifying one of these features, trace both `routes/web.php` and `routes/api.php` as well as its admin resource.

## Repository-specific cautions

- Use Laravel Boost's version-aware documentation search before changing Laravel, Filament, Livewire, or Tailwind behavior.
- Use Filament's Artisan generators for new Filament resources/pages/widgets and Laravel `make:*` generators for framework classes.
- Add validation through Form Request classes for API/controller input; Livewire/Filament actions still require validation and authorization at their server-side action boundary.
- Use Tailwind CSS 3 syntax. Alpine is already provided by Livewire; do not add a second Alpine bundle.
- `GEMINI.md`, `AGENTS.md`, `.agent/rules`, and `.kiro/steering` contain overlapping and sometimes stale command/architecture notes. Source code, registered Artisan commands, and this file take precedence when those documents conflict.
