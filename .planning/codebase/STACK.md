# Technology Stack

**Analysis Date:** 2026-01-24

## Languages

**Primary:**
- PHP 8.2+ (actual: 8.5.2) - Backend application logic, controllers, models, services
- JavaScript (ES modules) - Frontend asset bundling via Vite

**Secondary:**
- SQL - Database queries via Eloquent ORM

## Runtime

**Environment:**
- PHP 8.2+ (Laravel 12 requirement)
- Laravel 12.0 framework

**Package Manager:**
- Composer - PHP dependency management
- npm - JavaScript dependency management
- Lockfiles: `composer.lock` (present), `package-lock.json` (implied)

## Frameworks

**Core:**
- Laravel Framework 12.0 - Full-stack web framework with routing, ORM, migrations, authentication
- Vite 7.0.7 - Frontend build tool and dev server

**Build/Frontend:**
- Tailwind CSS 4.0.0 - Utility-first CSS framework
- @tailwindcss/vite 4.0.0 - Tailwind CSS integration with Vite
- laravel-vite-plugin 2.0.0 - Laravel integration for Vite

**Development/CLI:**
- Laravel Pail 1.2.2 - Real-time log viewing
- Laravel Boost 1.8 - Development optimization and MCP server support
- Laravel Tinker 2.10.1 - REPL for interactive command line

## Key Dependencies

**Critical:**
- laravel/framework 12.0 - Core framework
- laravel/tinker 2.10.1 - Interactive shell for testing code

**Frontend:**
- axios 1.11.0 - HTTP client for API requests
- @tailwindcss/vite 4.0.0 - CSS framework build integration

**Development:**
- fakerphp/faker 1.23 - Test data generation
- mockery/mockery 1.6 - Mocking library for testing
- nunomaduro/collision 8.6 - Enhanced error reporting
- phpunit/phpunit 11.5.3 - Testing framework
- laravel/pint 1.24 - Code style fixer (PSR-12)
- laravel/sail 1.41 - Docker development environment
- concurrently 9.0.1 - Run multiple npm scripts in parallel

## Configuration

**Environment:**
- `.env` file for configuration (see `.env.example` template)
- App Name: "ShoeMoney Skills To Pay The Bills"
- Debug mode: Enabled in development (`APP_DEBUG=true`)
- Locale: English (en_US)

**Build:**
- `vite.config.js` - Vite build configuration
  - Entry points: `resources/css/app.css`, `resources/js/app.js`
  - Plugins: Laravel Vite plugin, Tailwind CSS
  - Watch ignores: `**/storage/framework/views/**`

**Code Style:**
- `.editorconfig` - Enforces consistent coding standards
  - Charset: UTF-8
  - Line endings: LF
  - Indentation: 4 spaces
  - Insert final newline: enabled

## Platform Requirements

**Development:**
- PHP 8.2 or higher
- Composer installed
- npm/Node.js (Vite requirement)
- SQLite (default) or MySQL 8.0+ / MariaDB 10.3+ configured

**Production:**
- PHP 8.2+
- MySQL 8.0 or MariaDB 10.3 (SQLite for simple deployments)
- Redis (optional, for caching/sessions)
- File storage or S3 compatible storage (AWS S3, Cloudflare R2)

## Database Configuration

**Default Connection:**
- SQLite for development (`database/database.sqlite`)
- MySQL/MariaDB for production

**Environment Variables:**
- `DB_CONNECTION` - Database driver (sqlite, mysql, mariadb)
- `DB_HOST` - Database host
- `DB_PORT` - Database port
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database user
- `DB_PASSWORD` - Database password

## Asset Pipeline

**Input:**
- `resources/css/app.css` - Main stylesheet
- `resources/js/app.js` - Main JavaScript entry

**Processing:**
- Tailwind CSS v4 processing via @tailwindcss/vite
- JavaScript bundling with Vite

**Output:**
- Built assets served by Vite dev server (dev) or as static files (production)

## Development Workflow

**Available Scripts:**
- `composer setup` - Complete project setup (install dependencies, generate key, migrate, build assets)
- `composer dev` - Run development environment with concurrent processes:
  - PHP artisan serve (API server on :8000)
  - PHP artisan queue:listen (background jobs)
  - PHP artisan pail (log monitoring)
  - npm run dev (Vite dev server)
- `composer test` - Run test suite with PHPUnit
- `npm run build` - Build production assets
- `npm run dev` - Start Vite dev server

**Database:**
- Migrations in `database/migrations/`
- Seeders in `database/seeders/`
- Models in `app/Models/`

---

*Stack analysis: 2026-01-24*
