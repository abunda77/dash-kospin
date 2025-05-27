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
- `web` guard for regular users
- `admin` guard for administrative users

### Panel Structure

1. **Admin Panel** (`/admin` route)
   - Full access to cooperative management
   - Uses `admin` guard
   - Organized into navigation groups (Data Nasabah, Tabungan, Deposito, Pinjaman, etc.)

2. **User Panel** (`/user` route)
   - Limited functionality for regular users
   - Uses `web` guard

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