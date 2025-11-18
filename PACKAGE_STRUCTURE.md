# Laravel Exception Notifier - Package Structure

Complete directory structure and file organization for the `damku999/exception-notifier` package.

## 📁 Directory Structure

```
exception-notifier/
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       ├── ClearExceptionRateLimits.php
│   │       ├── ExceptionRateLimitStatus.php
│   │       └── TestExceptionEmail.php
│   ├── Facades/
│   │   └── ExceptionNotifier.php
│   ├── Services/
│   │   ├── ExceptionNotifierService.php
│   │   └── MailService.php (optional - uses Laravel Mail by default)
│   ├── Exceptions/
│   │   └── JsonExceptionHandler.php
│   ├── Mail/
│   │   └── ExceptionNotificationMail.php
│   ├── Config/
│   │   └── exception_notifier.php
│   └── ExceptionNotifierServiceProvider.php
├── resources/
│   └── views/
│       ├── exception_notification.blade.php
│       └── exception_suppression_notice.blade.php
├── tests/
│   ├── Unit/
│   │   ├── ExceptionNotifierServiceTest.php
│   │   ├── JsonExceptionHandlerTest.php
│   │   └── RateLimitingTest.php
│   ├── Feature/
│   │   ├── ExceptionEmailTest.php
│   │   ├── CommandsTest.php
│   │   └── IntegrationTest.php
│   └── TestCase.php
├── docs/
│   ├── installation.md
│   ├── configuration.md
│   ├── usage.md
│   ├── customization.md
│   └── troubleshooting.md
├── .github/
│   ├── workflows/
│   │   ├── tests.yml
│   │   ├── code-style.yml
│   │   └── static-analysis.yml
│   └── ISSUE_TEMPLATE/
│       ├── bug_report.md
│       └── feature_request.md
├── composer.json
├── phpunit.xml
├── pint.json
├── phpstan.neon
├── rector.php
├── README.md
├── CHANGELOG.md
├── LICENSE.md
├── CONTRIBUTING.md
├── SECURITY.md
└── .gitignore
```

## 📄 File Descriptions

### Core Files

#### `src/ExceptionNotifierServiceProvider.php`
Service provider that registers the package with Laravel.

**Responsibilities:**
- Register `ExceptionNotifierService` as singleton
- Register `JsonExceptionHandler` with dependencies
- Register Artisan commands
- Publish configuration file
- Publish view templates
- Merge default configuration

**Key Methods:**
```php
public function register(): void
public function boot(): void
protected function publishConfig(): void
protected function publishViews(): void
protected function registerCommands(): void
```

#### `src/Facades/ExceptionNotifier.php`
Laravel facade for easy access to ExceptionNotifierService.

**Usage:**
```php
use Damku999\ExceptionNotifier\Facades\ExceptionNotifier;

ExceptionNotifier::notify($exception);
ExceptionNotifier::getRateLimitStatus();
```

### Services

#### `src/Services/ExceptionNotifierService.php`
Main service handling exception notifications and rate limiting.

**Source:** `app/Services/ExceptionNotifierService.php` (Alliance-Auth)

**Changes for Package:**
- Remove dependency on `ConfigHelper` (use direct config access)
- Make `MailService` dependency optional (fallback to Laravel Mail)
- Add branding configuration to package config
- Update config keys from `constants.MAIL_VIRTUAL` to package config

**Key Methods:**
- `notify(Throwable $exception): void`
- `shouldNotify(Throwable $exception): bool`
- `generateSignature(Throwable $exception): string`
- `isRateLimitExceeded(string $signature): bool`
- `getRateLimitStatus(): array`
- `clearRateLimits(?string $signature = null): void`
- `formatStackTrace(Throwable $exception): array`
- `getRequestContext(): array`
- `getUserContext(): ?array`

#### `src/Services/MailService.php` (Optional)
Simplified mail service for sending exception emails.

**Note:** This is optional. The package can use Laravel's Mail facade directly.

**If included:**
```php
public function sendEmail(array $data): void
```

**Alternative:** Use Laravel Mail directly in `ExceptionNotifierService`:
```php
Mail::to($recipients)->send(new ExceptionNotificationMail($data));
```

### Exception Handling

#### `src/Exceptions/JsonExceptionHandler.php`
Main exception handler that integrates with Laravel's exception handling.

**Source:** `app/Exceptions/JsonExceptionHandler.php` (Alliance-Auth)

**Changes for Package:**
- Update namespace to `Damku999\ExceptionNotifier\Exceptions`
- Update service injection to use package namespace
- Keep all logic intact (already properly designed)

**Key Methods:**
```php
public function __construct(ExceptionNotifierService $notifierService)
public function handle(Throwable $e): JsonResponse
private function buildErrorResponse(int $status, string $message, array $data = []): JsonResponse
```

### Mail Classes

#### `src/Mail/ExceptionNotificationMail.php`
Laravel Mailable for exception notification emails.

**New File** (replaces generic `SendEmailCommon`):
```php
namespace Damku999\ExceptionNotifier\Mail;

use Illuminate\Mail\Mailable;

class ExceptionNotificationMail extends Mailable
{
    public function __construct(
        public array $data
    ) {}

    public function build(): self
    {
        return $this->subject($this->data['subject'])
                    ->view('exception-notifier::exception_notification')
                    ->with('data', $this->data);
    }
}
```

### Console Commands

#### `src/Console/Commands/ClearExceptionRateLimits.php`
Command to clear rate limits.

**Source:** `app/Console/Commands/ClearExceptionRateLimits.php` (Alliance-Auth)

**Changes:**
- Update namespace
- Update service injection
- Keep all logic intact

#### `src/Console/Commands/ExceptionRateLimitStatus.php`
Command to view rate limit status.

**Source:** `app/Console/Commands/ExceptionRateLimitStatus.php` (Alliance-Auth)

**Changes:**
- Update namespace
- Update service injection
- Keep all logic intact

#### `src/Console/Commands/TestExceptionEmail.php`
Command to send test exception emails.

**Source:** `app/Console/Commands/TestExceptionEmail.php` (Alliance-Auth)

**Changes:**
- Update namespace
- Update service injection
- Keep all logic intact

### Views

#### `resources/views/exception_notification.blade.php`
Main exception notification email template.

**Source:** `resources/views/emails/exception_notification.blade.php` (Alliance-Auth)

**Changes:**
- Update logo path handling to use package assets
- Make branding fully configurable via config
- Keep all styling and structure intact

#### `resources/views/exception_suppression_notice.blade.php`
Rate limit suppression notification template.

**Source:** `resources/views/emails/exception_suppression_notice.blade.php` (Alliance-Auth)

**Changes:**
- Update logo path handling
- Update Artisan command names to match package
- Keep all styling and structure intact

### Configuration

#### `src/Config/exception_notifier.php`
Complete package configuration file.

**Source:** `config/exception_notifier.php` (Alliance-Auth)

**Changes:**
- Add branding configuration section
- Update fallback recipients
- Add package-specific defaults
- Keep all existing options

**New branding section:**
```php
'branding' => [
    'email_logo' => 'vendor/exception-notifier/logo.png',
    'primary_color' => '#007bff',
    'text_color' => '#333333',
    'footer_text' => 'Exception Notifier - Laravel Package',
    'support_email' => env('MAIL_FROM_ADDRESS', 'support@example.com'),
],
```

## 🔧 Migration Guide from Alliance-Auth

### Files to Extract

1. **Services:**
   - `app/Services/ExceptionNotifierService.php` → `src/Services/ExceptionNotifierService.php`

2. **Exception Handlers:**
   - `app/Exceptions/JsonExceptionHandler.php` → `src/Exceptions/JsonExceptionHandler.php`

3. **Console Commands:**
   - `app/Console/Commands/ClearExceptionRateLimits.php` → `src/Console/Commands/ClearExceptionRateLimits.php`
   - `app/Console/Commands/ExceptionRateLimitStatus.php` → `src/Console/Commands/ExceptionRateLimitStatus.php`
   - `app/Console/Commands/TestExceptionEmail.php` → `src/Console/Commands/TestExceptionEmail.php`

4. **Views:**
   - `resources/views/emails/exception_notification.blade.php` → `resources/views/exception_notification.blade.php`
   - `resources/views/emails/exception_suppression_notice.blade.php` → `resources/views/exception_suppression_notice.blade.php`

5. **Configuration:**
   - `config/exception_notifier.php` → `src/Config/exception_notifier.php`

### Required Changes

#### 1. Namespace Updates
```php
// Before (Alliance-Auth)
namespace App\Services;

// After (Package)
namespace Damku999\ExceptionNotifier\Services;
```

#### 2. Remove Project-Specific Dependencies

**Remove `ConfigHelper`:**
```php
// Before
use App\Helpers\ConfigHelper;
$config = ConfigHelper::getProductConfig($product);

// After
$config = config('exception_notifier.branding');
```

**Remove `constants.php` dependencies:**
```php
// Before
$supportEmail = config('constants.MAIL_VIRTUAL');

// After
$supportEmail = config('exception_notifier.fallback_recipients');
```

#### 3. Update Service Provider Registration

Create `ExceptionNotifierServiceProvider`:
```php
namespace Damku999\ExceptionNotifier;

use Illuminate\Support\ServiceProvider;

class ExceptionNotifierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/Config/exception_notifier.php', 'exception_notifier');

        // Register services
        $this->app->singleton(Services\ExceptionNotifierService::class);
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/Config/exception_notifier.php' => config_path('exception_notifier.php'),
        ], 'exception-notifier-config');

        // Publish views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'exception-notifier');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/exception-notifier'),
        ], 'exception-notifier-views');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\ClearExceptionRateLimits::class,
                Console\Commands\ExceptionRateLimitStatus::class,
                Console\Commands\TestExceptionEmail::class,
            ]);
        }
    }
}
```

#### 4. Update View References

```php
// Before
return view('emails.exception_notification', $data);

// After
return view('exception-notifier::exception_notification', $data);
```

#### 5. Simplify MailService

Option A: Use Laravel Mail directly:
```php
use Illuminate\Support\Facades\Mail;
use Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail;

Mail::to($recipients)->send(new ExceptionNotificationMail($data));
```

Option B: Include simplified MailService in package.

### Testing Changes

Create test suite in `tests/`:
- Unit tests for `ExceptionNotifierService`
- Unit tests for `JsonExceptionHandler`
- Feature tests for email sending
- Feature tests for Artisan commands
- Integration tests for rate limiting

## 📦 Publishing to GitHub

### Repository Setup

1. **Create GitHub repository:**
   ```bash
   gh repo create damku999/exception-notifier --public --description "Production-ready exception notification system for Laravel 12+"
   ```

2. **Initialize repository:**
   ```bash
   git init
   git add .
   git commit -m "Initial release v1.0.0"
   git branch -M main
   git remote add origin https://github.com/damku999/exception-notifier.git
   git push -u origin main
   ```

3. **Create release tag:**
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```

### Packagist Submission

1. Go to https://packagist.org/
2. Click "Submit Package"
3. Enter repository URL: `https://github.com/damku999/exception-notifier`
4. Submit and verify

### GitHub Actions

Set up automated testing and code quality checks in `.github/workflows/`.

## 🚀 Next Steps

1. Extract and refactor all source files
2. Create service provider
3. Write comprehensive tests
4. Set up CI/CD pipeline
5. Create documentation
6. Publish to GitHub
7. Submit to Packagist
8. Announce on Laravel News, Reddit, Twitter

---

**Package Name:** `damku999/exception-notifier`
**Namespace:** `Damku999\ExceptionNotifier`
**Version:** 2.0.0
**License:** MIT
**PHP:** ^8.2
**Laravel:** ^12.0
**Author:** Darshan Baraiya
