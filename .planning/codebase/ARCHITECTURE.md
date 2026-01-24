# Architecture

**Analysis Date:** 2026-01-24

## Pattern Overview

**Overall:** Laravel Monolithic Web Application with MVC + Frontend Build Pipeline

**Key Characteristics:**
- Server-side routing with Blade templating
- Model-View-Controller separation using Laravel Framework 12
- Component-based frontend with Tailwind CSS and Vite build system
- RESTful HTTP routing with optional command-line interface support
- Database-driven architecture with migrations and seeders

## Layers

**Presentation Layer:**
- Purpose: Render HTML views and serve static assets
- Location: `resources/views/`, `resources/css/`, `resources/js/`
- Contains: Blade templates, CSS/Tailwind styles, JavaScript entry points
- Depends on: Routes, Controllers, Models (via view data)
- Used by: Web browsers, HTTP clients

**Routing Layer:**
- Purpose: Map HTTP requests to controllers and define application entry points
- Location: `routes/web.php`, `routes/console.php`
- Contains: Route definitions for web requests and console commands
- Depends on: Controllers
- Used by: HTTP request dispatcher

**Controller Layer:**
- Purpose: Handle HTTP requests, coordinate business logic, and return responses
- Location: `app/Http/Controllers/`
- Contains: Base Controller class (abstract foundation), specific route handlers
- Depends on: Models, Services, Repositories
- Used by: Router, Middleware

**Model Layer:**
- Purpose: Represent database entities and define data relationships
- Location: `app/Models/`
- Contains: Eloquent ORM models with relationships, casts, and mass assignment rules
- Depends on: Database schema, Factory definitions
- Used by: Controllers, Services, Queries

**Service/Business Logic Layer:**
- Purpose: Encapsulate domain logic and coordinate multiple models
- Location: `app/Services/` (if used; not currently populated)
- Contains: Reusable business logic independent of HTTP context
- Depends on: Models, external services
- Used by: Controllers, Commands

**Data Access Layer:**
- Purpose: Manage database connections, migrations, and data seeding
- Location: `database/migrations/`, `database/seeders/`, `database/factories/`
- Contains: Schema definitions, test data factories, database seeders
- Depends on: Database configuration
- Used by: Models, Tests, Commands

**Service Provider Layer:**
- Purpose: Register services, configure bindings, and bootstrap the application
- Location: `app/Providers/`, `bootstrap/providers.php`
- Contains: Service providers for application initialization
- Depends on: Configuration files, framework services
- Used by: Application bootstrap

**Configuration Layer:**
- Purpose: Centralize environment-specific and feature configuration
- Location: `config/`
- Contains: Database, cache, queue, session, mail, auth, logging configurations
- Depends on: Environment variables
- Used by: All layers

## Data Flow

**HTTP Request Lifecycle:**

1. Web server receives HTTP request to `public/index.php`
2. Composer autoloader registers all classes via PSR-4 namespaces
3. Application bootstrap (`bootstrap/app.php`) initializes Laravel Application instance
4. Service providers in `bootstrap/providers.php` register services
5. Middleware processes request (not currently configured in middleware section)
6. Router matches request to route definition in `routes/web.php`
7. Route dispatches to Controller action
8. Controller queries Model layer via Eloquent ORM
9. Model retrieves data from database
10. Controller processes data and prepares view context
11. View renderer renders Blade template with context data
12. Response returned to client

**Frontend Asset Pipeline:**

1. Developer runs `npm run dev` (Vite development server) or `npm run build` (production build)
2. Vite processes entry points: `resources/css/app.css` and `resources/js/app.js`
3. Tailwind CSS processes styles with JIT compilation against source files
4. Compiled assets placed in `public/build/` with manifest
5. View includes asset manifest to link processed resources
6. Browser loads and executes processed CSS and JavaScript

**State Management:**
- Session state stored in `sessions` table (database-driven)
- Application state managed via service container and facades
- User authentication state stored in `users` table with password hashing
- Cache data stored in `cache` table (configurable via `config/cache.php`)
- Password reset tokens stored in `password_reset_tokens` table

## Key Abstractions

**Model:**
- Purpose: Represents database entity with attributes, relationships, and accessor/mutator methods
- Examples: `app/Models/User.php`
- Pattern: Eloquent ORM - extends `Illuminate\Database\Eloquent\Model`
- Features: Mass assignment fillable properties, attribute casting, factory relationships

**Route:**
- Purpose: Binds HTTP method and path pattern to controller action
- Examples: `routes/web.php` defines `GET /` → welcome view
- Pattern: Fluent routing interface with method chaining
- Features: Named routes, middleware attachment, parameter binding

**Controller:**
- Purpose: Orchestrates request handling and response generation
- Examples: `app/Http/Controllers/Controller.php` (base class)
- Pattern: Base controller provides common functionality; specific controllers extend it
- Features: Request injection, view data passing, middleware support

**Service Provider:**
- Purpose: Bootstraps application services during initialization
- Examples: `app/Providers/AppServiceProvider.php`
- Pattern: Extends `Illuminate\Support\ServiceProvider` with `register()` and `boot()` methods
- Features: Service registration, dependency binding, deferred loading

**Factory:**
- Purpose: Generates test/seed data for models
- Examples: `database/factories/UserFactory.php`
- Pattern: Uses `HasFactory` trait in model, implements factory class
- Features: Attribute generation with Faker, model state definitions

## Entry Points

**Web Entry Point:**
- Location: `public/index.php`
- Triggers: HTTP requests from web server
- Responsibilities: Load autoloader, bootstrap application, capture and handle request

**Application Bootstrap:**
- Location: `bootstrap/app.php`
- Triggers: Invoked by public entry point
- Responsibilities: Create Application instance, configure routing/middleware/exceptions

**Service Provider Bootstrap:**
- Location: `bootstrap/providers.php`
- Triggers: Called during Application construction
- Responsibilities: Define which service providers to load during initialization

**Web Routing Entry:**
- Location: `routes/web.php`
- Triggers: Route matching for HTTP requests
- Responsibilities: Define routes and map to controllers/views

**Console Entry:**
- Location: `routes/console.php`
- Triggers: Artisan command invocation
- Responsibilities: Define scheduled tasks and console commands

## Error Handling

**Strategy:** Centralized exception handling via Application exception handler

**Patterns:**
- Framework catches all exceptions during request processing
- Exception handler configured in `bootstrap/app.php` (currently empty)
- HTTP exceptions automatically converted to appropriate status codes
- Model not found exceptions converted to 404 responses
- Database errors trigger exception handling
- Validation errors returned with error messages
- Custom exception handlers can be defined in exception configuration

## Cross-Cutting Concerns

**Logging:**
- Framework configured via `config/logging.php`
- Single channel driver (default, can use Monolog stacks)
- Logs written to `storage/logs/`

**Validation:**
- Form validation handled via Illuminate\Validation (not currently used in views)
- Validation rules can be defined in controllers or form requests

**Authentication:**
- Guards configured in `config/auth.php`
- User provider uses Eloquent with `app/Models/User.php`
- Sessions stored in database via `sessions` table
- Password hashing via `Illuminate\Hashing\BcryptHasher` (configured in User model casts)

---

*Architecture analysis: 2026-01-24*
