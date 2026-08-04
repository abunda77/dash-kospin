# Dash-Kospin

Laravel 11 / PHP 8.2+ savings-and-loans cooperative dashboard. Filament 3 admin/member panels, Livewire 3, Tailwind 3, Vite 5. `CLAUDE.md` has deeper architecture notes; `GEMINI.md` holds Laravel Boost conventions. Trust source code over the many root-level `*.md` docs when they conflict.

## Setup and dev commands
- First-time: `composer install`, `npm ci`, copy `.env.example` → `.env`, `php artisan key:generate`, configure DB, `php artisan migrate`, `php artisan storage:link`, `npm run build`.
- `.env.example` defaults to SQLite with database-backed sessions, cache, and queues — migrations are required before anything works. Don't assume MySQL/Redis are active.
- `composer dev` runs PHP server + `queue:listen --tries=1` + Pail + Vite together.
- After env/config changes: `php artisan optimize:clear`. Frontend build: `npm run build` (no npm lint/typecheck/test scripts exist).

## Quality checks
- Format: `vendor/bin/pint` (or `vendor/bin/pint --dirty` for changed files only); check with `vendor/bin/pint --test`.
- Tests: `php artisan test`, single method via `php artisan test --filter=<name>`, suite via `--testsuite=Feature`.
- `phpunit.xml` does **not** override `DB_CONNECTION` — tests hit the configured database. `tests/Feature/ExampleTest.php` is a 1,000-request stress test; don't use it as a smoke test.

## Architecture gotchas
- Two Filament panels: `/admin` (guard `admin`, `App\Models\Admin`, discovers `app/Filament/*`) and `/user` (guard `web`, `App\Models\User`, discovers only `app/Filament/User/*`). Keep resources/pages/widgets in the matching discovery path; user panel scopes via the authenticated user's profile, not Shield.
- `Profile` uses `id_user` as its Eloquent primary key while the `profiles` table also has its own `id`. `Tabungan.id_profile` → `profiles.id`; `Pinjaman.profile_id` and `Deposito.id_user` → `profiles.id_user`. Check both migrations and relations before joining; don't "normalize" these keys.
- Most models use custom primary keys (`id_pinjaman`, `id_pelunasan`, `id_gadai`, `id_cicilan_emas`, etc.) — inspect the model before assuming `id`.
- `app/helpers.php` is autoloaded via composer `files`.
- `App\Providers\EventServiceProvider` exists but is **not** listed in `bootstrap/providers.php`; its webhook listener mappings may be inactive — verify before relying on them. Webhooks also fire from observers registered in `AppServiceProvider`.
- `routes/console.php` schedules monthly interest via `monthlyOn(date('t'), '23:59')` — the day is computed at schedule-load time, so verify before treating it as true last-of-month. DB backup runs every 4h; barcode log cleanup weekly (90 days). Preserve these when touching scheduler commands.
- API controllers (`app/Http/Controllers/Api`) return hand-built response envelopes, not Eloquent Resources — follow the neighboring controller's shape. API exceptions render via `bootstrap/app.php`.
- Barcode/QRIS and Makan Bergizi features span models, web + API routes, and Filament resources — trace both route files plus the admin resource when modifying.

## Constraints
- Don't change dependencies or add top-level directories without approval; follow neighboring Laravel/Filament patterns.
- Use `php artisan make:*` / Filament generators with `--no-interaction`. Add env vars to `.env.example` and config files; never call `env()` outside `config/`.
- Use Form Request classes for API/controller validation; validate + authorize at the server-side action boundary for Livewire/Filament actions.
- Tailwind 3 syntax only; Alpine comes with Livewire — don't add a second Alpine bundle.
- Some artisan commands are interactive unless passed `--all`, `--dry-run`, `--force`, or `--no-interaction`; run `php artisan help <command>` first for repair/cleanup/export commands.
