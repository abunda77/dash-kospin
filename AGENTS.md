# Repository Guidelines

## Project Structure & Module Organization
- pp/ contains domain logic; keep admin panels in pp/Filament, HTTP glue in pp/Http, services/integrations in pp/Services, and shared helpers in pp/Helpers.
- Routes reside in outes/web.php and outes/api.php. UI assets live under esources/views, esources/js, and esources/css; built files are emitted by Vite into public/.
- Database migrations, factories, and seeders stay inside database/. Tests mirror code under 	ests/Feature and 	ests/Unit.

## Build, Test, and Development Commands
- composer install & 
pm install bootstrap PHP and JS dependencies.
- composer dev spins up the full dev stack (HTTP server, queue listener, logs, Vite) through 
px concurrently.
- php artisan migrate --seed refreshes schemas with demo data; run php artisan optimize:clear when tweaking config or env.
- 
pm run dev watches assets; 
pm run build produces production bundles.
- php artisan test runs PHPUnit; php artisan test --testsuite=Feature narrows scope during debugging.

## Coding Style & Naming Conventions
- Follow PSR-12; .editorconfig enforces 4-space indentation, UTF-8, and LF endings.
- Format PHP with ./vendor/bin/pint; respect Tailwind ordering defined in 	ailwind.config.js.
- Use PascalCase for classes and Filament resources, snake_case for tables, and dot-notation for Blade views (ilament.pages.dashboard).
- Prefer php artisan make:* generators so namespaces, contracts, and stubs remain consistent.

## Testing Guidelines
- Cover Filament pages, API endpoints, and queued jobs with Feature tests; reserve Unit tests for services and helpers.
- Leverage factories in database/factories and Laravel fakes for notifications, queues, and events.
- Add a regression test for every bug fix and organise specs under folders that mirror the production namespace.

## Commit & Pull Request Guidelines
- Use concise, imperative commit subjects (dd: deposito export command, ix: queue retry logic); squash noise before pushing.
- PRs should state the problem, the solution, and any schema or config impact, plus screenshots or payload samples for UI/API work.
- Link the related ticket, list manual verification steps, and ensure php artisan test and 
pm run build succeed before requesting review.

## Environment & Security Notes
- Never commit .env*; mirror new variables in .env.example and document defaults in config/*.php.
- When Redis is unavailable locally or in CI, fall back to QUEUE_CONNECTION=sync and disable Octane-specific settings in the test profile.
