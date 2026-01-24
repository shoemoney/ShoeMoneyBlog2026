# External Integrations

**Analysis Date:** 2026-01-24

## APIs & External Services

**Email/Notification Services:**
- Postmark - Email delivery service (optional)
  - SDK/Client: Laravel built-in mail transport
  - Auth: `POSTMARK_API_KEY` env var
  - Config: `config/services.php`

- Resend - Email API (optional)
  - SDK/Client: Laravel built-in mail transport
  - Auth: `RESEND_API_KEY` env var
  - Config: `config/services.php`

- AWS SES - Amazon Simple Email Service (optional)
  - SDK/Client: Laravel built-in mail transport
  - Auth: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`
  - Region: `AWS_DEFAULT_REGION` (default: us-east-1)
  - Config: `config/services.php`

**Messaging/Communication:**
- Slack - Slack notifications (optional)
  - SDK/Client: Laravel built-in notification channel
  - Auth: `SLACK_BOT_USER_OAUTH_TOKEN`
  - Config: `config/services.php`
  - Default channel: `SLACK_BOT_USER_DEFAULT_CHANNEL` env var

## Data Storage

**Databases:**
- SQLite (default for development)
  - Connection: `DB_CONNECTION=sqlite`
  - File: `database/database.sqlite`
  - Client: Laravel Eloquent ORM
  - Config: `config/database.php`

- MySQL 8.0+ (recommended for production)
  - Connection: `DB_CONNECTION=mysql`
  - Client: Laravel Eloquent ORM
  - Config: `config/database.php`
  - Env vars: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - Charset: utf8mb4 (default)
  - Collation: utf8mb4_unicode_ci (default)

- MariaDB 10.3+ (alternative to MySQL)
  - Connection: `DB_CONNECTION=mariadb`
  - Client: Laravel Eloquent ORM
  - Config: `config/database.php`

**File Storage:**
- Local filesystem (default)
  - Driver: `local`
  - Path: `storage/app/private/`
  - Config: `config/filesystems.php`

- AWS S3 / S3-Compatible (optional)
  - Driver: `s3`
  - Auth: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`
  - Bucket: `AWS_BUCKET`
  - Endpoint: `AWS_ENDPOINT` (for Cloudflare R2 or similar)
  - Region: `AWS_DEFAULT_REGION`
  - Config: `config/filesystems.php`
  - Env var: `FILESYSTEM_DISK` (default: local)

**Caching:**
- File-based caching (default development)
  - Store: `file`
  - Env var: `CACHE_STORE`
  - Config: `config/cache.php`

- Database caching (optional)
  - Store: `database`
  - Table: `cache` (default)
  - Env var: `CACHE_STORE`

- Memcached (optional)
  - Store: `memcached`
  - Host: `MEMCACHED_HOST` (127.0.0.1)
  - Env var: `CACHE_STORE`

- Redis (optional)
  - Store: `redis`
  - Client: phpredis (configured by `REDIS_CLIENT`)
  - Connection: `config/database.php` redis section
  - Env vars: `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`

## Authentication & Identity

**Auth Provider:**
- Custom Laravel authentication
  - Implementation: Session-based with Eloquent user provider
  - Guard: `web` (session driver)
  - User model: `App\Models\User` (configurable via `AUTH_MODEL` env var)
  - Config: `config/auth.php`

**Session Storage:**
- Database sessions (default: `SESSION_DRIVER=file`)
  - Table: `sessions` (when using database driver)
  - Lifetime: 120 minutes (configurable via `SESSION_LIFETIME`)
  - Encryption: Disabled by default (`SESSION_ENCRYPT=false`)
  - Config: `config/session.php`

**Password Reset:**
- Database token storage (default)
  - Table: `password_reset_tokens` (configurable)
  - Expiry: 60 minutes
  - Throttle: 60 seconds between requests
  - Config: `config/auth.php`

## Broadcasting

**Real-Time Events:**
- Log driver (default - disables broadcasting)
  - Env var: `BROADCAST_CONNECTION=log`
  - Config: `config/broadcasting.php` (not modified, uses default)

## Queue & Job Processing

**Background Jobs:**
- Database queue (default)
  - Driver: `database`
  - Table: `jobs`
  - Retry after: 90 seconds
  - Config: `config/queue.php`
  - Run: `php artisan queue:listen`

**Alternative Queues (configured but not enabled):**
- Redis queue
  - Driver: `redis`
  - Connection: redis config
  - Config: `config/queue.php`

- Beanstalkd queue
  - Driver: `beanstalkd`
  - Host: `BEANSTALKD_QUEUE_HOST`
  - Config: `config/queue.php`

- AWS SQS
  - Driver: `sqs`
  - Auth: AWS credentials
  - Prefix: `SQS_PREFIX` env var
  - Region: `AWS_DEFAULT_REGION`
  - Config: `config/queue.php`

**Failed Jobs:**
- Database storage (default)
  - Driver: `database-uuids`
  - Table: `failed_jobs`
  - Config: `config/queue.php`

## Monitoring & Observability

**Error Tracking:**
- Not detected - Application does not currently use Sentry, Bugsnag, or similar

**Logs:**
- Single file logging (default)
  - Driver: `stack` (aggregates multiple channels)
  - Channel: `single`
  - Level: `debug`
  - Deprecation warnings: `null` (disabled)
  - Config: `config/logging.php`
  - Storage: `storage/logs/laravel.log`

**Availability:**
- Laravel Pail (1.2.2) - Real-time log viewing via CLI
  - Command: `php artisan pail --timeout=0`

## CI/CD & Deployment

**Hosting:**
- Not detected in current configuration - Configured for local development

**Docker:**
- Laravel Sail (1.41) - Lightweight Docker development environment
  - Available but not required
  - Config: `docker-compose.yml` (generated)
  - Run: `./vendor/bin/sail up`

## Maintenance

**Maintenance Mode:**
- File driver (default)
  - Env var: `APP_MAINTENANCE_DRIVER`
  - Storage: `storage/framework/maintenance.php`
  - Alternative: Database driver (commented in `.env.example`)

## Development Tools

**Code Analysis & Performance:**
- Laravel Boost (1.8) - Development optimization
  - MCP Server support
  - Config: `.gemini/settings.json` (configured with laravel-boost MCP server)

**Testing Setup:**
- PHPUnit 11.5.3
- Mockery 1.6 for mocking
- Faker 1.23 for test data
- Test database: In-memory SQLite (`:memory:`)
- Config: `phpunit.xml`

## Environment Variables Summary

**Critical Env Vars:**
- `APP_NAME` - Application name
- `APP_KEY` - Encryption key (base64 encoded)
- `APP_ENV` - Environment (local, testing, production)
- `APP_DEBUG` - Debug mode toggle
- `APP_URL` - Application base URL

**Database:**
- `DB_CONNECTION` - Driver selection
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

**File Storage:**
- `FILESYSTEM_DISK` - Default disk (local, s3)
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, `AWS_ENDPOINT`

**Session & Cache:**
- `SESSION_DRIVER` - Session storage
- `CACHE_STORE` - Cache storage
- `QUEUE_CONNECTION` - Queue driver

**Email:**
- `MAIL_MAILER` - Mail driver
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`
- `POSTMARK_API_KEY`, `RESEND_API_KEY`

**Slack:**
- `SLACK_BOT_USER_OAUTH_TOKEN`
- `SLACK_BOT_USER_DEFAULT_CHANNEL`

---

*Integration audit: 2026-01-24*
