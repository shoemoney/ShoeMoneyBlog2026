# Coding Conventions

**Analysis Date:** 2026-01-24

## Naming Patterns

**Files:**
- PHP class files: PascalCase in singular form (e.g., `User.php`, `UserFactory.php`, `AppServiceProvider.php`)
- Database migration files: Timestamp prefix with snake_case description (e.g., `0001_01_01_000000_create_users_table.php`)
- Route files: lowercase with clear purpose (e.g., `web.php`, `console.php`)

**Functions/Methods:**
- snake_case for all functions and methods (e.g., `test_that_true_is_true()`, `test_the_application_returns_a_successful_response()`, `definition()`, `unverified()`)
- Test methods follow pattern: `test_<what_is_being_tested>()` (e.g., `test_the_application_returns_a_successful_response()`)
- Accessor/mutator methods use camelCase with action verb (e.g., `register()`, `boot()`, `casts()`)

**Variables:**
- Private/protected properties: camelCase with $ prefix (e.g., `$password`, `$fillable`, `$hidden`)
- Configuration array keys: snake_case (e.g., `'email_verified_at'`, `'remember_token'`)
- Static properties: camelCase (e.g., `$password`)

**Types/Classes:**
- Class names: PascalCase (e.g., `User`, `TestCase`, `UserFactory`)
- Namespace segments: PascalCase (e.g., `App\Models\User`, `Database\Factories\UserFactory`, `Tests\Unit`, `Tests\Feature`)
- Traits: PascalCase with suffixed Trait or pattern-based (e.g., `HasFactory`, `Notifiable`, `WithoutModelEvents`)

## Code Style

**Formatting:**
- Editor config: `.editorconfig` enforces consistent formatting
- 4-space indentation (space-based, not tabs)
- UTF-8 character set
- LF line endings
- Final newline at end of file
- Trim trailing whitespace on all files except Markdown

**Linting:**
- Laravel Pint (`laravel/pint` ^1.24) - PHP code style linter
- Follows Laravel coding standards and PSR standards
- Enforced via composer dependency

## Import Organization

**Order:**
1. Namespace declaration (no blank line after)
2. Use statements (grouped logically)
3. Blank line
4. Class declaration

**Pattern from files:**
```php
<?php

namespace App\Models;

// Framework/core imports first
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Then class definition
class User extends Authenticatable
```

**Path Aliases:**
- None detected; uses full namespace paths from PSR-4 autoloading
- PSR-4 namespace prefixes defined in `composer.json`:
  - `App\` → `app/`
  - `Database\Factories\` → `database/factories/`
  - `Database\Seeders\` → `database/seeders/`
  - `Tests\` → `tests/`

## Error Handling

**Patterns:**
- Laravel Framework handles most exception throwing
- Test assertions use Laravel's assertion helpers: `$this->assertTrue()`, `$response->assertStatus()`
- Database transactions handled through Laravel Eloquent and Schema builders
- No explicit try-catch blocks in examined code; relies on framework-level error handling

## Logging

**Framework:** Laravel's built-in logging system via `Illuminate\Support\Facades\Log` (not examined in detail but configured)

**Patterns:**
- Not extensively demonstrated in current codebase
- Framework provides Monolog integration
- Configuration available in `config/logging.php`

## Comments

**When to Comment:**
- PHPDoc comments on classes and public methods (shown in models, factories, tests)
- Commented-out code allowed temporarily (e.g., `// use Illuminate\Contracts\Auth\MustVerifyEmail;` in User.php)

**JSDoc/TSDoc:**
- Not applicable; PHP project uses PHPDoc standards
- Type hints provided through PHPDoc `@var`, `@return`, `@extends` annotations
- Example from `UserFactory.php`:
  ```php
  /**
   * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
   */
  ```

## Function Design

**Size:**
- Keep methods small and focused (most examined methods 2-20 lines)
- Factories and models delegate to framework methods

**Parameters:**
- Use typed parameters where possible (return types shown: `array`, `void`, `static`)
- Return type hints always present on public methods
- Example: `public function definition(): array`

**Return Values:**
- Explicitly declare return types
- Array returns documented with `@return array<string, mixed>` or `@return array<string, string>`
- Methods return self for fluent interface patterns (e.g., `unverified()` returns `static`)

## Module Design

**Exports:**
- Classes exported through namespace structure; no barrel files
- Each file contains one primary class
- Example: `app/Models/User.php` exports `App\Models\User` class

**Barrel Files:**
- Not used; direct imports from specific files
- PSR-4 autoloading handles class discovery

## Model Patterns

**Eloquent Models:**
- Protected properties for mass assignment control: `$fillable`, `$hidden`
- Type casting through `casts()` method returning array:
  ```php
  protected function casts(): array
  {
      return [
          'email_verified_at' => 'datetime',
          'password' => 'hashed',
      ];
  }
  ```
- Traits used for common functionality: `HasFactory`, `Notifiable`

**Factories:**
- `definition()` method returns array of attributes
- Modifier methods for variations (e.g., `unverified()`)
- Use Faker for fake data generation: `fake()->name()`, `fake()->unique()->safeEmail()`

---

*Convention analysis: 2026-01-24*
