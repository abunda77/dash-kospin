---
trigger: always_on
---

---
inclusion: always
---

# Technology Stack

## Core Stack
- **Laravel 11** (PHP 8.2+): Minimalist structure, use modern PHP features
- **Filament 3**: Primary admin interface - prefer Filament resources over custom controllers
- **Livewire**: For reactive components (via Filament and standalone)
- **TailwindCSS + Shadcn UI**: Styling - use utility classes, avoid custom CSS

## Database
- **SQLite** (dev) / **MySQL/MariaDB** (prod)
- **Eloquent ORM**: Always use relationships, scopes, and eager loading
- **Redis**: Optional for caching/sessions in production

## Key Libraries
- **DOMPDF**: PDF generation - use `PdfHelper` class for consistent formatting
- **Spatie Laravel Permission**: RBAC - check permissions in policies
- **Laravel Sanctum**: API auth - use for barcode/webhook endpoints
- **Hashids**: Public ID obfuscation - never expose database IDs publicly

## Development Commands

```bash
# Start all services
composer dev

# Individual services  
php artisan serve
php artisan queue:listen
php artisan pail
npm run dev

# Database
php artisan migrate
php artisan db:seed

# Cache management
php artisan optimize:clear  # Clear all caches
php artisan optimize        # Production optimization

# Custom commands
php artisan report:export-loan [--options]
php artisan report:export-savings [--options]
php artisan report:check-progress [key]
php artisan report:cleanup [--hours=24]
php artisan hitung:bunga-tabungan
```

## Code Style Rules
- **PSR-12** standard (enforced by Laravel Pint)
- Use type hints for parameters and return types
- Prefer dependency injection over facades in services
- Use named routes, never hardcode URLs
- Keep controllers thin - move logic to services
- Use form requests for validation

## Environment
- `.env` for all environment-specific config
- Never commit `.env` - use `.env.example` as template
- SQLite for dev, MySQL/MariaDB for production
- Queue driver: database (dev), Redis (prod recommended)