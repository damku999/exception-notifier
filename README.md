# Laravel Exception Notifier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/damku999/exception-notifier.svg?style=flat-square)](https://packagist.org/packages/damku999/exception-notifier)
[![Total Downloads](https://img.shields.io/packagist/dt/damku999/exception-notifier.svg?style=flat-square)](https://packagist.org/packages/damku999/exception-notifier)
[![License](https://img.shields.io/packagist/l/damku999/exception-notifier.svg?style=flat-square)](https://packagist.org/packages/damku999/exception-notifier)

> **Version 2.0** - Major upgrade with Laravel 12+ support! 🚀

**Laravel Exception Notifier** is a production-ready exception notification system for Laravel 12+ applications. Get instant email alerts when exceptions occur in your application with intelligent rate limiting, customizable templates, and comprehensive context data.

## 🆕 What's New in v2.0

- ✨ **Laravel 12+ Support** - Modern `bootstrap/app.php` pattern
- ✨ **PHP 8.2+ Required** - Latest PHP features and performance
- ✨ **Per-Signature Rate Limiting** - Each exception tracked separately
- ✨ **Critical Exception Bypass** - Important errors always notify
- ✨ **Enhanced Bot Detection** - Better false positive filtering
- ✨ **Zero-Loop Guarantee** - Fixed infinite loop bug with dependency injection
- ✨ **Email Branding** - Customizable logo, colors, and footer

**Upgrading from v1.x?** See [UPGRADE.md](UPGRADE.md) for migration guide.

## ✨ Features

- 🚨 **Instant Email Notifications** - Get notified immediately when exceptions occur
- 🎯 **Smart Rate Limiting** - Per-exception-signature rate limiting to prevent email spam
- 🔥 **Critical Exception Bypass** - Critical exceptions always bypass rate limits
- 📊 **Rich Context Data** - Stack traces, request details, user information, and more
- 🎨 **Customizable Email Templates** - Beautiful, responsive HTML email templates
- 🤖 **Bot Detection** - Automatically ignore exceptions from bots and crawlers
- 🔧 **Artisan Commands** - Manage rate limits and test notifications via CLI
- 🌍 **Environment-Aware** - Silent mode in local environment during development
- 📝 **Detailed Logging** - All exceptions still logged even when email suppressed
- ⚡ **Zero Performance Impact** - Notifications wrapped in try-catch to never break your app

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 12.0 or higher
- Mail configuration (SMTP, Mailgun, SES, etc.)

## 📦 Installation

Install the package via Composer:

```bash
composer require damku999/exception-notifier
```

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="exception-notifier-config"
```

This will create `config/exception_notifier.php` with all available options.

### Publish Email Templates (Optional)

If you want to customize the email templates:

```bash
php artisan vendor:publish --tag="exception-notifier-views"
```

Templates will be published to `resources/views/vendor/exception-notifier/`.

### Publish Migrations (Optional)

If you want to use database-backed rate limiting:

```bash
php artisan vendor:publish --tag="exception-notifier-migrations"
php artisan migrate
```

## ⚙️ Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Enable exception email notifications (default: false)
EXCEPTION_EMAIL_ENABLED=true

# Silent mode in local environment (default: true)
EXCEPTION_EMAIL_SILENT_LOCAL=true

# Email recipients (comma-separated)
EXCEPTION_EMAIL_TO=admin@example.com,dev@example.com

# Rate limiting (default: 10 emails per hour per signature)
EXCEPTION_EMAIL_MAX_PER_HOUR=10
EXCEPTION_EMAIL_RATE_WINDOW=3600
```

### Basic Setup

Update your `bootstrap/app.php` to use the exception notifier:

```php
<?php

use Damku999\ExceptionNotifier\JsonExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            return app(JsonExceptionHandler::class)->handle($e);
        });
    })->create();
```

### Advanced Configuration

Edit `config/exception_notifier.php` for advanced options:

```php
return [
    // Enable/disable globally
    'enabled' => env('EXCEPTION_EMAIL_ENABLED', false),

    // Silent mode in local environment
    'silent_in_local' => env('EXCEPTION_EMAIL_SILENT_LOCAL', true),

    // Email recipients
    'recipients' => array_filter(array_map('trim', explode(',', env('EXCEPTION_EMAIL_TO', '')))),

    // Fallback recipients if none specified
    'fallback_recipients' => ['admin@example.com'],

    // Rate limiting
    'max_emails_per_signature_per_hour' => env('EXCEPTION_EMAIL_MAX_PER_HOUR', 10),
    'rate_limit_window' => env('EXCEPTION_EMAIL_RATE_WINDOW', 3600),

    // Ignored exceptions (won't send emails)
    'ignored_exceptions' => [
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        \Illuminate\Auth\AuthenticationException::class,
    ],

    // Critical exceptions (bypass rate limits)
    'critical_exceptions' => [
        \Illuminate\Database\QueryException::class,
    ],

    // Bot user agents to ignore
    'ignored_bots' => [
        'googlebot', 'bingbot', 'crawler', 'spider', 'bot',
    ],

    // Include context data in emails
    'include_request_data' => true,
    'include_user_data' => true,
    'include_stack_trace' => true,
    'max_stack_trace_depth' => 10,

    // Send suppression notice when rate limit reached
    'send_suppression_notice' => true,
];
```

## 🚀 Usage

### Automatic Exception Handling

Once configured in `bootstrap/app.php`, the package automatically catches and notifies you of exceptions:

```php
// Any uncaught exception will trigger an email notification
throw new \Exception('Something went wrong!');

// Validation exceptions are ignored by default (configurable)
throw ValidationException::withMessages(['email' => 'Invalid email']);

// Database exceptions are marked as critical (always sent)
DB::table('non_existent')->get(); // Triggers critical email
```

### Manual Exception Notification

You can manually trigger exception notifications:

```php
use Damku999\ExceptionNotifier\Facades\ExceptionNotifier;

try {
    // Your code
} catch (\Throwable $e) {
    ExceptionNotifier::notify($e);

    // Continue with your error handling
}
```

### Checking Rate Limits

```php
use Damku999\ExceptionNotifier\Facades\ExceptionNotifier;

// Check if rate limit exceeded for specific exception
$signature = ExceptionNotifier::generateSignature($exception);
$exceeded = ExceptionNotifier::isRateLimitExceeded($signature);

// Get current count for signature
$count = ExceptionNotifier::getRateLimitCount($signature);

// Get all rate limit statuses
$statuses = ExceptionNotifier::getRateLimitStatus();
```

## 🔧 Artisan Commands

### View Rate Limit Status

View current rate limit status for all exception signatures:

```bash
php artisan exception:rate-limit-status
```

Output:
```
┌──────────────────────────────────────────────────────────────┬───────┬─────┬──────────┐
│ Exception Signature                                          │ Count │ Max │ TTL (s)  │
├──────────────────────────────────────────────────────────────┼───────┼─────┼──────────┤
│ Exception:app/Http/Controllers/UserController.php:45         │ 8     │ 10  │ 2847     │
│ QueryException:app/Models/User.php:123                       │ 15    │ 10  │ 1523     │
└──────────────────────────────────────────────────────────────┴───────┴─────┴──────────┘
```

### Clear Rate Limits

Clear all rate limits:

```bash
php artisan exception:clear-rate-limits
```

Clear specific signature:

```bash
php artisan exception:clear-rate-limits --signature="Exception:app/Http/Controllers/UserController.php:45"
```

### Test Exception Emails

Send a test exception email:

```bash
php artisan exception:test
```

Send test with custom exception type:

```bash
php artisan exception:test --type=critical
```

## 📧 Email Templates

The package includes two beautiful, responsive email templates:

### Exception Notification Email

Sent when an exception occurs (within rate limits):

- **Exception Summary** - Class, message, file, line, signature
- **Stack Trace** - Formatted call stack with file/line numbers
- **Request Details** - URL, method, IP, user agent
- **User Context** - Authenticated user information
- **Environment Info** - Environment name and timestamp
- **Rate Limit Status** - Current count vs maximum allowed

### Rate Limit Suppression Email

Sent once when rate limit is reached:

- **Rate Limit Info** - Signature, max count, time remaining
- **What This Means** - Explanation of suppression
- **Action Required** - Steps to investigate and resolve
- **Helpful Commands** - CLI commands to manage rate limits

### Customizing Templates

Publish the views and edit them:

```bash
php artisan vendor:publish --tag="exception-notifier-views"
```

Templates location: `resources/views/vendor/exception-notifier/`

### Customizing Email Branding

Override the branding configuration:

```php
// In your AppServiceProvider or config
config([
    'exception_notifier.branding' => [
        'email_logo' => 'images/logo.png',
        'primary_color' => '#007bff',
        'text_color' => '#333333',
        'footer_text' => 'Your Company Name',
        'support_email' => 'support@example.com',
    ],
]);
```

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

## 📊 Exception Signature Format

The package generates unique signatures for each exception using:

```
Format: ExceptionClass:FilePath:LineNumber
Example: Exception:app/Http/Controllers/UserController.php:45
```

This ensures:
- ✅ Same exception at same location = same signature
- ✅ Rate limiting works per unique error
- ✅ Different locations = different signatures

## 🔒 Security

### Preventing Infinite Loops

The package is designed to never break your application:

```php
// In JsonExceptionHandler
try {
    $this->notifierService->notify($e);
} catch (Throwable $notificationError) {
    // Silently fail if notification fails
    Log::error('Exception notification failed', [
        'error' => $notificationError->getMessage(),
    ]);
}
```

### Bot Protection

Automatically ignores exceptions from bots to prevent spam:

```php
'ignored_bots' => [
    'googlebot', 'bingbot', 'slurp', 'crawler', 'spider',
    'bot', 'facebookexternalhit', 'twitterbot', 'whatsapp',
    'telegram', 'curl', 'wget',
],
```

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/damku999/exception-notifier.git
cd exception-notifier

# Install dependencies
composer install

# Run tests
composer test

# Run code style checks
composer lint
```

## 📝 Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## 📄 License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.

## 🙏 Credits

- **Author**: Darshan Baraiya
- **GitHub**: [@damku999](https://github.com/damku999)
- **Built with**: Laravel 12, PHP 8.2

## 💡 Use Cases

Perfect for:

- 🏢 **Production Applications** - Monitor critical production errors
- 🔧 **Staging Environments** - Catch bugs before production
- 📊 **API Services** - Track API failures and exceptions
- 🚀 **Microservices** - Centralized exception monitoring
- 👥 **Team Collaboration** - Multiple developers receive alerts

## 🆘 Support

- 📖 [Documentation](https://github.com/damku999/exception-notifier/wiki)
- 🐛 [Issue Tracker](https://github.com/damku999/exception-notifier/issues)
- 💬 [Discussions](https://github.com/damku999/exception-notifier/discussions)

---

**Developed by [Darshan Baraiya](https://github.com/damku999)**
