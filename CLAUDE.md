# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Dash-Kospin is a web application for managing a Savings and Loans Cooperative (KoSPIN), built with Laravel 11 and Filament 3 for the admin panel. The application handles member management, savings accounts, loans, deposits, referrals, and other operational aspects of the cooperative.

## Technology Stack

- **Backend**: PHP 8.2+ with Laravel 11
- **Admin Panel**: Filament 3
- **Frontend**: TailwindCSS, Shadcn UI components
- **Database**: MySQL/MariaDB
- **Caching**: Redis
- **Performance**: Laravel Octane
- **API Authentication**: Laravel Sanctum
- **PDF Generation**: DOMPDF

## Development Commands

### Basic Setup

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build
```

### Development Mode

```bash
# Run all development services in parallel (server, queue, logs, vite)
composer dev

# Run only development server
php artisan serve

# Run only frontend asset compilation with hot reload
npm run dev

# Run queue worker
php artisan queue:listen --tries=1

# Watch logs in real-time
php artisan pail
```

### Database Operations

```bash
# Run migrations
php artisan migrate

# Refresh migrations (rollback and run again)
php artisan migrate:refresh

# Fresh migrations with seeders
php artisan migrate:fresh --seed

# Database backup
php artisan backup:run --only-db
```

### Testing & Quality Assurance

```bash
# Run tests
php artisan test

# Code formatting with Laravel Pint
vendor/bin/pint

# Check code style
vendor/bin/pint --test
```

### PDF Export Commands

```bash
# Export loan reports
php artisan report:export-loan
php artisan report:export-loan --type=transaction --start-date=2024-01-01 --end-date=2024-12-31

# Export savings reports
php artisan report:export-savings
php artisan report:export-savings --type=transaction --product=1

# Export deposit reports
php artisan report:export-deposit
php artisan report:export-deposit --status=active --jangka-waktu=12

# Export with public download links
php artisan report:export-loan --public

# Check export progress
php artisan report:check-progress

# Cleanup old report files
php artisan report:cleanup --hours=24
```

### Custom Artisan Commands

```bash
# Calculate monthly savings interest
php artisan hitung:bunga-tabungan

# Test PDF generation
php artisan test:pdf-generation

# Assign specific permissions
php artisan assign:keterlambatan-90-hari-permission
```

### Cache Management

```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Generate configuration cache
php artisan config:cache

# Clear view cache
php artisan view:clear

# Clear route cache
php artisan route:cache
```

### Filament Admin

```bash
# Generate Filament assets
php artisan filament:assets

# Cache Filament components
php artisan filament:cache-components
```

## Core Architecture

### Authentication System

The application has a multi-guard authentication system:
- `web` guard for regular users (User Panel)
- `admin` guard for administrative users (Admin Panel)
- `sanctum` guard for API authentication

### Panel Structure

1. **Admin Panel** (`/admin` route)
   - Full access to cooperative management
   - Uses `admin` guard with Admin model
   - Brand: "Kospin Sinara Artha" with custom logo
   - Primary color: Amber
   - Navigation groups: Data Nasabah, Data Karyawan, Tabungan, Deposito, Pinjaman, Settings
   - Includes FilamentShield for role-based permissions
   - Integrated plugins: API Service, Logger, Artisan commands, Activity Log, Health Monitor

2. **User Panel** (`/user` route)
   - Limited functionality for regular users
   - Uses `web` guard with User model
   - Registration enabled for new users
   - Primary color: Green
   - Minimal plugin configuration

### Key Models and Relationships

1. **User & Profile**
   - `User`: Authentication model with roles and permissions (using FilamentShield)
   - `Profile`: Detailed member information (linked to User model)

2. **Financial Products**
   - `Tabungan` (Savings): Member savings accounts
   - `Pinjaman` (Loans): Member loan accounts
   - `Deposito` (Time Deposits): Fixed-term deposits with interest

3. **Transactions**
   - `TransaksiTabungan`: Savings transactions (deposits/withdrawals)
   - `TransaksiPinjaman`: Loan transactions (disbursements/repayments)

4. **Product Configuration**
   - `ProdukTabungan`: Savings product types
   - `ProdukPinjaman`: Loan product types 
   - `BiayaBungaPinjaman`: Loan interest rates
   - `Denda`: Penalties configuration

5. **Specialized Loan Products**
   - `Gadai`: Pawn services
   - `KreditElektronik`: Electronic device loans
   - `CicilanEmas`: Gold installment loans

6. **Referral System**
   - `AnggotaReferral`: Referral members
   - `TransaksiReferral`: Referral transaction records
   - `SettingKomisi`: Commission settings

### Event System

The application uses Laravel's event system for various operations:
- Transaction events trigger webhooks for integration with other systems
- Deposit events handle automated calculations
- Background jobs process notifications (WhatsApp, email)

### Scheduled Tasks

Important scheduled tasks:
- Monthly interest calculation for savings accounts
- Regular database backups
- Birthday greeting notifications

### WhatsApp Integration

The application integrates with a WhatsApp API for:
- Sending payment reminders
- Birthday greetings
- Mass notifications to members/employees
- WhatsApp Gateway accessible via Admin Panel navigation (external link)

### PDF Report System

Advanced PDF export system with progress tracking:
- **Export Types**: Loans, Savings, Deposits (with transactions)
- **Features**: Date range filtering, product filtering, status filtering
- **Progress Monitoring**: Real-time progress tracking with web interface
- **Public Downloads**: Optional public storage with secure download URLs
- **Performance**: Chunked processing with configurable memory limits
- **Cleanup**: Automated cleanup of old report files
- **Web Interface**: Progress monitor at `/export-monitor`
- **API Endpoints**: Progress checking via `/export-progress/{key}`

## Data Flow Overview

1. **Savings Account Flow**:
   - Member profiles are created
   - Savings accounts are opened with specific product types
   - Transactions (deposits/withdrawals) are recorded
   - Monthly interest is calculated automatically
   - Transaction webhooks integrate with external systems

2. **Loan Flow**:
   - Member applies for loan
   - Loan is created with specified product type and interest rate
   - Collateral (if required) is recorded
   - Repayment transactions are tracked
   - Late payment penalties are applied automatically
   - Reminder notifications are sent for upcoming payments

3. **Deposit Flow**:
   - Fixed-term deposit is created
   - Interest is calculated based on rate and term
   - Auto-renewal is managed at maturity
   - Disbursement is processed on maturity date

## API Structure

The application provides RESTful API endpoints:

### Authentication Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User authentication
- `POST /api/logout` - User logout (requires auth)
- `POST /api/forgot-password` - Password reset request
- `POST /api/reset-password` - Password reset
- `PATCH /api/update-password` - Change password (requires auth)

### Protected Endpoints (require Sanctum authentication)
- `GET /api/user` - Get authenticated user info
- Profile management (`/api/profiles`)
- Savings operations (`/api/tabungan/*`)
- Loan operations (`/api/pinjaman/*`)
- Deposit operations (`/api/deposito/*`)
- Installment operations (`/api/angsuran/*`)

### Public Endpoints
- Region data (`/api/regions`)
- Mobile banners (`/api/banner-mobile/type/{type}`)
- Transaction history (`/api/mutasi/{no_tabungan}/{periode}`)
- Configuration (`/api/config/api-base-url`)

## File Management

### Report Downloads
- **Route**: `/download-report/{filename}`
- **Security**: PDF-only file type validation, basename sanitization
- **Storage**: `storage/app/public/reports/`

### Export Progress Monitoring
- **Web Interface**: `/export-monitor` - Real-time progress tracking
- **API Endpoint**: `/export-progress/{key}` - JSON progress data
- **Features**: Auto-refresh, progress bars, time estimates

## Development Workflow

### Before Making Changes
1. Always check existing code patterns and conventions
2. Use the same libraries and frameworks already in the project
3. Follow the established Filament Resource/Page patterns
4. Maintain consistency with existing navigation groups

### When Adding New Features
1. Consider whether it belongs in Admin or User panel
2. Use appropriate Filament components (Resources, Pages, Widgets)
3. Add necessary permissions via FilamentShield
4. Update navigation groups if needed
5. Add appropriate observers if the feature involves model events

### API Development
1. Add routes to `routes/api.php`
2. Use Sanctum authentication for protected endpoints
3. Follow existing controller patterns in `app/Http/Controllers/Api/`
4. Update Scramble documentation as needed