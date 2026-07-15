# Dash-Kospin

## Stack and entry points
- Laravel 11 / PHP 8.2+, Filament 3, Livewire 3, Tailwind 3, and Vite 5; PHP dependencies are locked in `composer.lock` and JS dependencies in `package-lock.json`.
- HTTP routing begins in `bootstrap/app.php`; browser/API routes are `routes/web.php` and `routes/api.php`, and recurring work is defined in `routes/console.php`.
- Filament has separate panels: admin at `/admin` using the `admin` guard (`app/Providers/Filament/AdminPanelProvider.php`) and member UI at `/user` using `web` (`app/Providers/Filament/UserPanelProvider.php`). Keep their Resources, Pages, and Widgets in their respective discovery paths.
- Vite compiles only `resources/css/app.css` and `resources/js/app.js`; use the `@` alias for `resources/js` imports.

## Local workflow
- First-time setup: `composer install`, `npm install`, copy `.env.example` to `.env`, run `php artisan key:generate`, then configure the database before `php artisan migrate`.
- `composer dev` starts the PHP server, `queue:listen --tries=1`, Pail, and Vite together. The default environment uses database-backed sessions, cache, and queues, so migrations are required.
- Use `php artisan migrate:fresh --seed` only when destructive database reset is intended. Environment/config changes require `php artisan optimize:clear`.
- Build frontend changes with `npm run build`; the project has no npm lint, typecheck, or test scripts.

## Quality checks
- Format PHP with `vendor/bin/pint`; check formatting without changes using `vendor/bin/pint --test`.
- Run focused tests with `php artisan test --filter=<TestName>` or a suite via `php artisan test --testsuite=Feature`; use `php artisan test` for the full suite.
- PHPUnit forces sync queues, array cache/mail/session but does **not** set a testing database connection; tests use the configured database unless explicitly overridden. The existing Feature `ExampleTest` issues 1,000 requests, so avoid treating it as a quick smoke test.

## Constraints
- Do not change dependencies or create new top-level application directories without approval; follow neighboring Laravel and Filament patterns.
- Use `php artisan make:* --no-interaction` for Laravel artifacts. Add environment variables to `.env.example` and configuration files; never call `env()` outside `config/`.
- The scheduler runs monthly savings-interest calculation plus duplicate cleanup, database backups every four hours, and weekly barcode scan-log cleanup; preserve these behaviors when modifying related commands.
- `GEMINI.md` contains the active Laravel Boost conventions; consult it for Laravel, Filament, Livewire, Tailwind, and test-specific rules.
