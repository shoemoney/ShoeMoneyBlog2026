# Codebase Structure

**Analysis Date:** 2026-01-24

## Directory Layout

```
ShoeMoneyVelle/
├── app/                          # Application source code
│   ├── Http/
│   │   └── Controllers/          # HTTP request handlers
│   ├── Models/                   # Eloquent ORM models
│   └── Providers/                # Service providers
├── bootstrap/                    # Application initialization
│   ├── app.php                   # Main application configuration
│   ├── providers.php             # Service provider registry
│   └── cache/                    # Framework bootstrap cache
├── config/                       # Configuration files
│   ├── app.php                   # Application name, timezone, etc.
│   ├── auth.php                  # Authentication guards and providers
│   ├── cache.php                 # Cache driver and stores
│   ├── database.php              # Database connections
│   ├── filesystems.php           # File storage configuration
│   ├── logging.php               # Logging channels and drivers
│   ├── mail.php                  # Mail driver configuration
│   ├── queue.php                 # Queue driver configuration
│   ├── services.php              # Third-party service credentials
│   └── session.php               # Session driver and lifetime
├── database/                     # Database schema and data
│   ├── migrations/               # Schema migration files
│   ├── seeders/                  # Database seeders
│   └── factories/                # Model factories for testing
├── public/                       # Web root served by server
│   ├── index.php                 # Application entry point
│   ├── .htaccess                 # Apache rewrite rules
│   └── build/                    # Compiled frontend assets (generated)
├── resources/                    # Frontend resources
│   ├── views/                    # Blade template files
│   ├── css/                      # Stylesheets (Tailwind CSS)
│   └── js/                       # JavaScript entry points
├── routes/                       # Application routing
│   ├── web.php                   # HTTP routes
│   └── console.php               # Console commands
├── storage/                      # Runtime data storage
│   ├── app/                      # Application file uploads
│   ├── framework/                # Framework-generated files
│   │   ├── cache/                # Framework cache data
│   │   ├── views/                # Compiled Blade templates
│   │   ├── sessions/             # Session files (if file driver used)
│   │   └── testing/              # Test-related files
│   └── logs/                     # Application log files
├── tests/                        # Test suite
│   ├── Feature/                  # Feature/integration tests
│   ├── Unit/                     # Unit tests
│   └── TestCase.php              # Base test class
├── vendor/                       # Composer dependencies (not committed)
├── .env                          # Environment variables (gitignored)
├── .env.example                  # Environment template
├── artisan                       # Laravel CLI tool
├── composer.json                 # PHP dependencies
├── composer.lock                 # Locked PHP dependencies
├── package.json                  # JavaScript dependencies
├── vite.config.js                # Vite build configuration
├── phpunit.xml                   # PHPUnit test configuration
└── .editorconfig                 # Editor formatting rules
```

## Directory Purposes

**app/**
- Purpose: All application source code organized by responsibility
- Contains: Models, Controllers, Services, Providers
- Key files: `app/Models/User.php`, `app/Http/Controllers/Controller.php`

**app/Http/Controllers/**
- Purpose: HTTP request handler classes that process input and return responses
- Contains: Base Controller class, specific controller implementations
- Key files: `app/Http/Controllers/Controller.php`

**app/Models/**
- Purpose: Eloquent ORM model definitions representing database entities
- Contains: Model classes with relationships, casts, and business logic
- Key files: `app/Models/User.php`

**app/Providers/**
- Purpose: Service provider classes that register and bootstrap application services
- Contains: AppServiceProvider for custom bindings
- Key files: `app/Providers/AppServiceProvider.php`

**bootstrap/**
- Purpose: Application initialization and bootstrapping
- Contains: Application factory and service provider registry
- Key files: `bootstrap/app.php`, `bootstrap/providers.php`

**config/**
- Purpose: Environment-aware configuration for all application features
- Contains: Configuration arrays for database, cache, queue, mail, auth, etc.
- Key files: `config/database.php`, `config/auth.php`, `config/cache.php`

**database/migrations/**
- Purpose: Version-controlled database schema changes
- Contains: Migration classes that create/modify database tables
- Key files: `database/migrations/0001_01_01_000000_create_users_table.php`

**database/seeders/**
- Purpose: Populate database with initial or test data
- Contains: Seeder classes that insert data
- Key files: `database/seeders/DatabaseSeeder.php`

**database/factories/**
- Purpose: Generate randomized test data for models
- Contains: Factory classes paired with models
- Key files: `database/factories/UserFactory.php`

**public/**
- Purpose: Web-accessible directory served by web server
- Contains: Entry point, static assets, compiled frontend builds
- Key files: `public/index.php`

**public/build/**
- Purpose: Compiled and optimized frontend assets (generated by build process)
- Contains: JavaScript bundles, CSS files, manifest
- Key files: `public/build/manifest.json` (tells app where assets are)

**resources/views/**
- Purpose: Blade template files for rendering HTML
- Contains: `.blade.php` files with HTML and PHP directives
- Key files: `resources/views/welcome.blade.php`

**resources/css/**
- Purpose: Tailwind CSS source files
- Contains: CSS with Tailwind directives and custom styles
- Key files: `resources/css/app.css`

**resources/js/**
- Purpose: JavaScript entry points and source code
- Contains: Modern JavaScript modules and Vite entry points
- Key files: `resources/js/app.js`, `resources/js/bootstrap.js`

**routes/**
- Purpose: Define application routing rules
- Contains: Route definitions for HTTP and console
- Key files: `routes/web.php`

**routes/web.php**
- Purpose: Map HTTP requests to controllers or closures
- Contains: Route definitions with methods, paths, and handlers
- Key files: Maps GET requests to controllers and views

**routes/console.php**
- Purpose: Define console commands and scheduled tasks
- Contains: Artisan command definitions
- Key files: Placeholder for custom commands

**storage/**
- Purpose: Runtime writable storage for application-generated data
- Contains: Uploaded files, cache, compiled views, sessions, logs
- Key files: `storage/logs/` directory contains application logs

**storage/framework/cache/**
- Purpose: Cache data storage (if file-based cache driver used)
- Contains: Serialized cache entries
- Key files: Automatically managed

**storage/framework/views/**
- Purpose: Compiled Blade template cache for performance
- Contains: PHP files compiled from `.blade.php` templates
- Key files: Automatically managed by framework

**storage/logs/**
- Purpose: Application log files for debugging and monitoring
- Contains: Daily or single log files depending on configuration
- Key files: `laravel.log` or dated log files

**tests/**
- Purpose: Automated test suite for validating application behavior
- Contains: Feature tests (high-level workflows) and unit tests (isolated logic)
- Key files: `tests/TestCase.php` (base class)

**tests/Feature/**
- Purpose: Integration tests for HTTP request flows
- Contains: Test classes inheriting from TestCase
- Key files: `tests/Feature/ExampleTest.php`

**tests/Unit/**
- Purpose: Unit tests for isolated components and methods
- Contains: Test classes for models, services, utilities
- Key files: `tests/Unit/ExampleTest.php`

**vendor/**
- Purpose: Composer-managed PHP dependencies (not committed)
- Contains: Laravel framework, libraries, and utilities
- Key files: `vendor/autoload.php`

## Key File Locations

**Entry Points:**
- `public/index.php`: Primary web application entry point
- `artisan`: Command-line tool for artisan commands
- `bootstrap/app.php`: Application factory and configuration
- `routes/web.php`: HTTP route definitions

**Configuration:**
- `config/app.php`: Application name, timezone, locale
- `config/database.php`: Database connection credentials
- `config/auth.php`: Authentication guards and user providers
- `.env`: Environment-specific configuration (development/production secrets)
- `.env.example`: Template for required environment variables

**Core Logic:**
- `app/Models/User.php`: User entity definition
- `app/Http/Controllers/Controller.php`: Base controller with shared functionality
- `app/Providers/AppServiceProvider.php`: Custom service registrations

**Presentation:**
- `resources/views/welcome.blade.php`: Welcome page template
- `resources/css/app.css`: Tailwind CSS stylesheet
- `resources/js/app.js`: JavaScript entry point

**Testing:**
- `tests/TestCase.php`: Base test class with setup/teardown
- `tests/Feature/ExampleTest.php`: Sample feature test
- `tests/Unit/ExampleTest.php`: Sample unit test
- `phpunit.xml`: PHPUnit configuration

**Database:**
- `database/migrations/0001_01_01_000000_create_users_table.php`: Initial schema
- `database/factories/UserFactory.php`: User test data factory
- `database/seeders/DatabaseSeeder.php`: Main seeder

## Naming Conventions

**Files:**
- PHP classes: PascalCase (e.g., `UserController.php`, `User.php`)
- Blade templates: snake_case (e.g., `welcome.blade.php`, `user_profile.blade.php`)
- JavaScript: camelCase (e.g., `app.js`, `bootstrap.js`)
- CSS: lowercase with hyphens (e.g., `app.css`)
- Migrations: timestamp prefix with description (e.g., `0001_01_01_000000_create_users_table.php`)

**Directories:**
- Feature folders: lowercase plural (e.g., `Models/`, `Controllers/`, `Views/`)
- Domain/feature namespaces: PascalCase (e.g., `App\Models\`, `App\Http\Controllers\`)

**Classes:**
- Model classes: Singular noun (e.g., `User`, `Post`, `Comment`)
- Controller classes: Singular noun + `Controller` suffix (e.g., `UserController`, `PostController`)
- Provider classes: Descriptive name + `Provider` suffix (e.g., `AppServiceProvider`)
- Test classes: Descriptive name + `Test` suffix (e.g., `UserControllerTest`)

**Methods:**
- camelCase for instance/class methods
- Standard Laravel conventions: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` for resource controllers

## Where to Add New Code

**New Feature - Full CRUD:**
- Controllers: `app/Http/Controllers/YourFeatureController.php`
- Model: `app/Models/YourModel.php`
- Views: `resources/views/yourfeature/` directory with `index.blade.php`, `create.blade.php`, etc.
- Routes: Add routes in `routes/web.php`
- Migration: `database/migrations/YYYY_MM_DD_HHMMSS_create_yourfeature_table.php`
- Tests: `tests/Feature/YourFeatureTest.php` and `tests/Unit/YourModelTest.php`

**New Model/Entity:**
- Model class: `app/Models/YourModel.php`
- Factory: `database/factories/YourModelFactory.php`
- Migration: `database/migrations/YYYY_MM_DD_HHMMSS_create_yourmodels_table.php`

**New Controller:**
- Implementation: `app/Http/Controllers/YourController.php`
- Extend: `App\Http\Controllers\Controller` base class

**Reusable Business Logic:**
- Service class: `app/Services/YourService.php`
- Import in controllers: `use App\Services\YourService;`

**Views/Templates:**
- Blade files: `resources/views/yourfeature/` with descriptive names
- Layouts: `resources/views/layouts/` for shared templates
- Components: `resources/views/components/` for reusable template components

**Utilities/Helpers:**
- Shared helpers: `app/Helpers/` directory
- Static utility classes: Follow namespace `App\Helpers\YourHelper`

**Tests:**
- Feature tests: `tests/Feature/YourFeatureTest.php`
- Unit tests: `tests/Unit/YourUnitTest.php`
- Base class: Extend `Tests\TestCase`

## Special Directories

**storage/framework/views/**
- Purpose: Compiled Blade template cache
- Generated: Yes (automatically by framework)
- Committed: No (should be gitignored, regenerated as needed)

**storage/framework/cache/**
- Purpose: File-based cache storage
- Generated: Yes (automatically by framework)
- Committed: No (runtime data)

**storage/logs/**
- Purpose: Application log files
- Generated: Yes (by application during runtime)
- Committed: No (runtime data)

**bootstrap/cache/**
- Purpose: Framework bootstrap cache for performance
- Generated: Yes (`php artisan config:cache`, `php artisan route:cache`)
- Committed: No (regenerated during deployment)

**public/build/**
- Purpose: Compiled frontend assets from Vite build
- Generated: Yes (`npm run build` during CI/deployment)
- Committed: No (built from source on deployment)

**vendor/**
- Purpose: Composer-managed dependencies
- Generated: Yes (`composer install` from composer.lock)
- Committed: No (lockfile committed, vendor excluded)

---

*Structure analysis: 2026-01-24*
