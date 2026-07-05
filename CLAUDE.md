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

# Run a single test file
php artisan test --filter=ExampleTest

# Code formatting with Laravel Pint
vendor/bin/pint

# Check code style
vendor/bin/pint --test
```

Note: the test suite currently contains only Laravel's `ExampleTest` stubs (in `tests/Feature` and `tests/Unit`); there is no meaningful test coverage yet. The Pint config lives in the project root (no `pint.json` preset).

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
# Calculate monthly savings interest (scheduled monthly on last day 23:59)
php artisan tabungan:hitung-bunga --all
# Remove duplicate interest entries after a run
php artisan tabungan:hitung-bunga --hapus-duplikat

# Test PDF generation
php artisan test:pdf-generation

# Assign specific permissions
php artisan permission:assign-keterlambatan-90-hari
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

> **Directory layout**: Filament Resources live flat under `app/Filament/Resources/<Name>Resource/` (one directory per resource, not split by panel). Admin-only pages and User-panel pages are separated by namespace instead: `app/Filament/Pages/` and `app/Filament/User/Pages/` respectively, with widgets in `app/Filament/Widgets/` and `app/Filament/User/Widgets/`. The `app/Filament/Pages/` directory holds a large set of custom pages (laporan reports, laporan keterlambatan, simulasi kredit, QRIS generators, mutasi viewers, etc.) registered through the AdminPanelProvider.

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

Important scheduled tasks (defined in `routes/console.php`):
- Monthly savings interest calculation (`tabungan:hitung-bunga --all`) — runs `monthlyOn` on the last day of the month at 23:59, then calls `--hapus-duplikat` to dedupe
- Database backup (`backup:run --only-db`) — every 4 hours
- Barcode scan log cleanup (`barcode:cleanup-logs --days=90`) — weekly on Sundays at 02:00

Note: birthday greetings and WhatsApp reminders are dispatched from Filament pages (e.g. `Birthday`, `KirimWA`, `Reminder`) as jobs, not via the scheduler.

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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.8
- filament/filament (FILAMENT) - v3
- laravel/framework (LARAVEL) - v11
- laravel/octane (OCTANE) - v2
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class `
    - `php artisan make:interface `


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v3 rules ===

## Tailwind 3

- Always use Tailwind CSS v3 - verify you're using only classes supported by this version.


=== filament/filament rules ===

## Filament
- Filament is used by this application, check how and where to follow existing application conventions.
- Filament is a Server-Driven UI (SDUI) framework for Laravel. It allows developers to define user interfaces in PHP using structured configuration objects. It is built on top of Livewire, Alpine.js, and Tailwind CSS.
- You can use the `search-docs` tool to get information from the official Filament documentation when needed. This is very useful for Artisan command arguments, specific code examples, testing functionality, relationship management, and ensuring you're following idiomatic practices.
- Utilize static `make()` methods for consistent component initialization.

### Artisan
- You must use the Filament specific Artisan commands to create new files or components for Filament. You can find these with the `list-artisan-commands` tool, or with `php artisan` and the `--help` option.
- Inspect the required options, always pass `--no-interaction`, and valid arguments for other options when applicable.

### Filament's Core Features
- Actions: Handle doing something within the application, often with a button or link. Actions encapsulate the UI, the interactive modal window, and the logic that should be executed when the modal window is submitted. They can be used anywhere in the UI and are commonly used to perform one-time actions like deleting a record, sending an email, or updating data in the database based on modal form input.
- Forms: Dynamic forms rendered within other features, such as resources, action modals, table filters, and more.
- Infolists: Read-only lists of data.
- Notifications: Flash notifications displayed to users within the application.
- Panels: The top-level container in Filament that can include all other features like pages, resources, forms, tables, notifications, actions, infolists, and widgets.
- Resources: Static classes that are used to build CRUD interfaces for Eloquent models. Typically live in `app/Filament/Resources`.
- Schemas: Represent components that define the structure and behavior of the UI, such as forms, tables, or lists.
- Tables: Interactive tables with filtering, sorting, pagination, and more.
- Widgets: Small component included within dashboards, often used for displaying data in charts, tables, or as a stat.

### Relationships
- Determine if you can use the `relationship()` method on form components when you need `options` for a select, checkbox, repeater, or when building a `Fieldset`:

<code-snippet name="Relationship example for Form Select" lang="php">
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author')
    ->required(),
</code-snippet>


## Testing
- It's important to test Filament functionality for user satisfaction.
- Ensure that you are authenticated to access the application within the test.
- Filament uses Livewire, so start assertions with `livewire()` or `Livewire::test()`.

### Example Tests

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1))
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Howdy',
        'email' => 'howdy@example.com',
    ]);
</code-snippet>

<code-snippet name="Testing Multiple Panels (setup())" lang="php">
    use Filament\Facades\Filament;

    Filament::setCurrentPanel('app');
</code-snippet>

<code-snippet name="Calling an Action in a Test" lang="php">
    livewire(EditInvoice::class, [
        'invoice' => $invoice,
    ])->callAction('send');

    expect($invoice->refresh())->isSent()->toBeTrue();
</code-snippet>


## Version 3 Changes To Focus On
- Resources are located in `app/Filament/Resources/` directory.
- Resource pages (List, Create, Edit) are auto-generated within the resource's directory - e.g., `app/Filament/Resources/PostResource/Pages/`.
- Forms use the `Forms\Components` namespace for form fields.
- Tables use the `Tables\Columns` namespace for table columns.
- A new `Filament\Forms\Components\RichEditor` component is available.
- Form and table schemas now use fluent method chaining.
- Added `php artisan filament:optimize` command for production optimization.
- Requires implementing `FilamentUser` contract for production access control.
</laravel-boost-guidelines>
