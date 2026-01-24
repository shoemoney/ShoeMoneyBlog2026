# Testing Patterns

**Analysis Date:** 2026-01-24

## Test Framework

**Runner:**
- PHPUnit 11.5.3
- Config: `phpunit.xml`
- Bootstraps through `vendor/autoload.php`

**Assertion Library:**
- PHPUnit's built-in assertions: `assertTrue()`, `assertStatus()`
- Laravel's HTTP testing assertions: `$response->assertStatus(200)`
- Laravel feature test helpers through `Illuminate\Foundation\Testing\TestCase`

**Run Commands:**
```bash
composer test              # Run all tests (executes: @php artisan config:clear --ansi && @php artisan test)
php artisan test           # Direct PHPUnit runner
php artisan test --watch   # Watch mode (likely supported)
php artisan test --coverage # Coverage report
```

## Test File Organization

**Location:**
- Separate from source code; co-located by test type
- Unit tests: `tests/Unit/`
- Feature tests: `tests/Feature/`

**Naming:**
- Files: `{TestSubject}Test.php` (e.g., `ExampleTest.php`)
- Classes: PascalCase with `Test` suffix (e.g., `ExampleTest`)
- Methods: `test_<description>()` in snake_case (e.g., `test_that_true_is_true()`, `test_the_application_returns_a_successful_response()`)

**Structure:**
```
tests/
├── Unit/
│   └── ExampleTest.php
├── Feature/
│   └── ExampleTest.php
└── TestCase.php
```

## Test Structure

**Suite Organization:**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
```

**Patterns:**
- One test method per test concept
- Each test method prefixed with `test_` keyword
- PHPDoc comment describing what is being tested
- Return type always `void`
- Single assertion per method or grouped related assertions
- Use of `$this->get()` for HTTP GET requests (Laravel TestCase provides)
- HTTP assertions use fluent interface: `$response->assertStatus(200)`

## Mocking

**Framework:**
- Mockery 1.6 available (dev dependency)
- Laravel's built-in mocking for HTTP requests, Database, etc.

**Patterns:**
Not extensively demonstrated in current test files, but available through:
- `Illuminate\Foundation\Testing\RefreshDatabase` trait (shown commented in Feature test)
- Laravel's model factory system for test data

**What to Mock:**
- External HTTP calls
- Database interactions (if using RefreshDatabase trait)
- File system operations
- Time-based operations

**What NOT to Mock:**
- Framework core functionality (use as-is)
- Application routes and controllers (test the real thing)
- Model relationships when testing models

## Fixtures and Factories

**Test Data:**
- Use Laravel Factories: `User::factory()->create()`
- Pattern from `DatabaseSeeder`:
  ```php
  User::factory()->create([
      'name' => 'Test User',
      'email' => 'test@example.com',
  ]);
  ```
- Factories support modifier methods: `User::factory()->unverified()->create()`
- Bulk creation: `User::factory(10)->create()`

**Location:**
- Factories: `database/factories/` (PSR-4 namespace: `Database\Factories\`)
- Each factory corresponds to a model: `UserFactory` for `User` model
- Factory methods return arrays of attributes

## Test Environment Configuration

**Configuration:**
From `phpunit.xml`:
- Test environment: `APP_ENV=testing`
- Database: SQLite in-memory (`:memory:`)
- Cache store: array (in-memory)
- Mail mailer: array (captured in memory)
- Queue connection: sync (runs immediately)
- Session driver: array
- Disabled features: Pulse, Telescope, Nightwatch

**Environment Setup:**
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    ...
</php>
```

## Coverage

**Requirements:** Not enforced; no coverage threshold specified in config

**View Coverage:**
```bash
php artisan test --coverage              # Generate coverage report
php artisan test --coverage-html report/ # HTML report to report/ directory
```

**Source Coverage:**
- Configured in `phpunit.xml` to include `app/` directory for coverage analysis

## Test Types

**Unit Tests:**
- Location: `tests/Unit/`
- Scope: Individual classes/functions in isolation
- Example: `ExampleTest.php` tests basic assertions
- Approach: Direct method calls, minimal dependencies

**Feature Tests:**
- Location: `tests/Feature/`
- Scope: Full request/response cycles through application
- Example: HTTP GET request to `/` route returning 200
- Approach: Use `$this->get()`, `$this->post()`, etc. for HTTP requests
- Assertions: HTTP status codes, response content, redirects

**E2E Tests:**
- Not currently used in codebase
- Could use Laravel's HTTP testing or external tools like Dusk

## Common Patterns

**Async Testing:**
Not detected in current codebase.

**Error Testing:**
Demonstrated through HTTP assertions:
```php
$response = $this->get('/');
$response->assertStatus(200);  // Pass if 200, fail otherwise
// Implied: $response->assertStatus(404) would test error responses
```

**Database Testing:**
- RefreshDatabase trait available (shown commented in Feature test example):
  ```php
  use Illuminate\Foundation\Testing\RefreshDatabase;
  ```
- When used, runs migrations before each test and rolls back after
- Ensures test isolation and clean database state

**JSON Response Testing:**
- Not demonstrated but available through Laravel TestCase: `$response->json()`
- Can assert JSON structure and content

## Seeding in Tests

**Pattern:**
- Use DatabaseSeeder in tests or custom seeders
- Call `$this->seed()` in feature tests with RefreshDatabase
- Or directly call factories in tests:
  ```php
  $user = User::factory()->create();
  ```

---

*Testing analysis: 2026-01-24*
