# Upgrade Guide: v1.x → v2.0

Complete guide for upgrading from exception-notifier v1.x to v2.0 with Laravel 12 support.

## 📋 Overview

**Version 2.0** is a major release with breaking changes:

| Feature | v1.x | v2.0 |
|---------|------|------|
| PHP | 7.4+ / 8.0+ | **8.2+** ⚠️ |
| Laravel | 8, 9, 10, 11 | **12+** ⚠️ |
| Rate Limiting | No | **Per-signature** ✨ |
| Critical Exceptions | No | **Yes** ✨ |
| Bot Detection | Basic | **Enhanced** ✨ |
| Fail-Safe Handling | Basic | **Zero-loop guarantee** ✨ |

## ⚠️ Breaking Changes

### 1. PHP Version Requirement
```bash
# v1.x
"php": ">=7.4 || ^8.0"

# v2.0
"php": "^8.2"
```

**Action Required:** Upgrade to PHP 8.2+ before updating package.

### 2. Laravel Version Requirement
```bash
# v1.x
"illuminate/support": "^8.0|^9.0|^10.0|^11.0"

# v2.0
"illuminate/support": "^12.0"
```

**Action Required:** Upgrade to Laravel 12 before updating package.

### 3. Bootstrap/App.php Structure

**v1.x (Laravel 8-11):**
```php
// app/Exceptions/Handler.php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // Old exception handling
    }
}
```

**v2.0 (Laravel 12):**
```php
// bootstrap/app.php
use Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler;

return Application::configure(basePath: dirname(__DIR__))
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            return app(JsonExceptionHandler::class)->handle($e);
        });
    })->create();
```

**Action Required:** Update bootstrap/app.php (see step-by-step below).

### 4. Configuration Structure

**New config options in v2.0:**
```php
// config/exception_notifier.php

// NEW: Rate limiting strategy
'rate_limit_strategy' => 'per_signature',
'max_emails_per_signature_per_hour' => 10,
'rate_limit_window' => 3600,
'rate_limit_cache_key' => 'exception_notifier_rate',

// NEW: Critical exceptions (bypass rate limits)
'critical_exceptions' => [
    \Illuminate\Database\QueryException::class,
],

// NEW: Enhanced bot detection
'ignored_bots' => [
    'googlebot', 'bingbot', 'crawler', 'spider', 'bot',
    'facebookexternalhit', 'twitterbot', 'whatsapp',
    'telegram', 'curl', 'wget',
],

// NEW: Email branding
'branding' => [
    'email_logo' => 'vendor/exception-notifier/logo.png',
    'primary_color' => '#007bff',
    'text_color' => '#333333',
    'footer_text' => 'Exception Notifier',
    'support_email' => env('MAIL_FROM_ADDRESS', 'support@example.com'),
],

// NEW: Rate limit suppression notice
'send_suppression_notice' => true,

// NEW: Context data controls
'include_request_data' => true,
'include_user_data' => true,
'include_stack_trace' => true,
'max_stack_trace_depth' => 10,
```

**Action Required:** Republish and review configuration.

## 🔄 Step-by-Step Upgrade Process

### Step 1: Verify System Requirements

```bash
# Check PHP version (must be 8.2+)
php -v

# Check Laravel version (must be 12.x)
php artisan --version

# If not on Laravel 12, upgrade Laravel first:
# https://laravel.com/docs/12.x/upgrade
```

**Output should show:**
```
PHP 8.2.x or 8.3.x
Laravel Framework 12.x.x
```

### Step 2: Backup Your Project

```bash
# Create backup
git stash
git checkout -b upgrade-exception-notifier-v2

# Or backup files manually
cp config/exception_notifier.php config/exception_notifier.php.backup
cp app/Exceptions/Handler.php app/Exceptions/Handler.php.backup
```

### Step 3: Update Composer Dependencies

Edit `composer.json`:

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "damku999/exception-notifier": "^2.0"
    }
}
```

Update dependencies:

```bash
composer update damku999/exception-notifier --with-all-dependencies
```

**Expected Output:**
```
Package operations: 0 installs, 1 update, 0 removals
  - Updating damku999/exception-notifier (v1.0.1 => v2.0.0)
```

### Step 4: Republish Configuration

```bash
# Republish config (keep backup)
php artisan vendor:publish --tag="exception-notifier-config" --force
```

This will overwrite `config/exception_notifier.php` with v2.0 structure.

### Step 5: Merge Configuration Changes

Compare your backup with the new config:

```bash
# If you have custom ignored exceptions
diff config/exception_notifier.php.backup config/exception_notifier.php
```

**Merge your custom settings:**
```php
// Example: Add your custom ignored exceptions
'ignored_exceptions' => [
    // v2.0 defaults
    \Illuminate\Validation\ValidationException::class,
    \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,

    // Your custom exceptions (from v1.x)
    \App\Exceptions\YourCustomException::class,
],
```

### Step 6: Update Bootstrap/App.php

**Remove old handler** (if you had custom exception handling):

```php
// app/Exceptions/Handler.php - remove custom exception notifier code
```

**Update bootstrap/app.php:**

```php
<?php

use Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler;
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
        // Add exception notifier
        $exceptions->render(function (Throwable $e) {
            return app(JsonExceptionHandler::class)->handle($e);
        });
    })->create();
```

### Step 7: Update Environment Variables

Add new v2.0 environment variables to `.env`:

```env
# Existing (keep these)
EXCEPTION_EMAIL_ENABLED=true
EXCEPTION_EMAIL_SILENT_LOCAL=true
EXCEPTION_EMAIL_TO=admin@example.com

# NEW in v2.0: Rate limiting
EXCEPTION_EMAIL_MAX_PER_HOUR=10
EXCEPTION_EMAIL_RATE_WINDOW=3600
```

### Step 8: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 9: Test Installation

```bash
# Test exception email
php artisan exception:test

# Check rate limit status (NEW command)
php artisan exception:rate-limit-status

# Clear rate limits (NEW command)
php artisan exception:clear-rate-limits
```

### Step 10: Review Email Templates (Optional)

If you customized email templates in v1.x, republish views:

```bash
php artisan vendor:publish --tag="exception-notifier-views" --force
```

New templates location:
- `resources/views/vendor/exception-notifier/exception_notification.blade.php`
- `resources/views/vendor/exception-notifier/exception_suppression_notice.blade.php` (NEW)

### Step 11: Deploy to Staging

Test on staging environment before production:

```bash
# On staging
composer install --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Trigger test exception
php artisan exception:test
```

### Step 12: Monitor Production

After deploying to production:

```bash
# Monitor logs
tail -f storage/logs/laravel.log

# Check rate limit status
php artisan exception:rate-limit-status

# Check for failed emails
grep -i "exception notification failed" storage/logs/laravel.log
```

## 🆕 New Features in v2.0

### 1. Per-Signature Rate Limiting

```php
// Automatically enabled in v2.0
// Each unique exception (Class:File:Line) has its own rate limit
'max_emails_per_signature_per_hour' => 10,
```

**Benefits:**
- ✅ Different exceptions don't affect each other's limits
- ✅ Critical exceptions bypass rate limits
- ✅ Suppression notice sent when limit reached

### 2. Critical Exceptions

```php
// These ALWAYS send emails, bypassing rate limits
'critical_exceptions' => [
    \Illuminate\Database\QueryException::class,
    \PDOException::class,
    // Add your critical exceptions
],
```

### 3. Enhanced Bot Detection

```php
// Expanded bot list
'ignored_bots' => [
    'googlebot', 'bingbot', 'slurp', 'crawler', 'spider',
    'bot', 'facebookexternalhit', 'twitterbot', 'whatsapp',
    'telegram', 'curl', 'wget',
],
```

### 4. Email Branding Customization

```php
'branding' => [
    'email_logo' => 'images/your-logo.png',
    'primary_color' => '#007bff',
    'text_color' => '#333333',
    'footer_text' => 'Your Company Name',
    'support_email' => 'support@example.com',
],
```

### 5. Rate Limit Management Commands

```bash
# View current rate limits
php artisan exception:rate-limit-status

# Clear all rate limits
php artisan exception:clear-rate-limits

# Clear specific signature
php artisan exception:clear-rate-limits --signature="..."
```

### 6. Suppression Notice Email

When rate limit is reached, recipients get ONE notification email explaining:
- What happened
- Why notifications are suppressed
- When they'll resume
- How to clear rate limits manually

## 🔧 Configuration Migration

### Minimal Configuration (v1.x → v2.0)

If you used minimal config in v1.x:

```php
// v1.x minimal config
return [
    'enabled' => true,
    'recipients' => ['admin@example.com'],
    'ignored_exceptions' => [
        \Illuminate\Validation\ValidationException::class,
    ],
];
```

Add v2.0 defaults:

```php
// v2.0 minimal config
return [
    // Keep your v1.x settings
    'enabled' => true,
    'recipients' => ['admin@example.com'],
    'ignored_exceptions' => [
        \Illuminate\Validation\ValidationException::class,
    ],

    // Add v2.0 rate limiting (use defaults)
    'max_emails_per_signature_per_hour' => 10,
    'rate_limit_window' => 3600,

    // Add v2.0 critical exceptions (optional)
    'critical_exceptions' => [
        \Illuminate\Database\QueryException::class,
    ],
];
```

## 🐛 Troubleshooting Upgrade

### Issue: "Class JsonExceptionHandler not found"

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

### Issue: "Too few arguments to function __construct()"

This is the bug v2.0 fixes! Make sure you're using:

```php
// ✅ CORRECT (v2.0)
return app(JsonExceptionHandler::class)->handle($e);

// ❌ WRONG (causes infinite loop)
return (new JsonExceptionHandler)->handle($e);
```

### Issue: "Method render does not exist"

You're still using old Laravel 11 exception handler structure.

**Solution:** Update `bootstrap/app.php` as shown in Step 6.

### Issue: Emails not sending after upgrade

**Check:**
```bash
# 1. Config cache
php artisan config:clear

# 2. Mail configuration
php artisan tinker
>>> config('mail.default')

# 3. Package enabled
php artisan tinker
>>> config('exception_notifier.enabled')

# 4. Test email
php artisan exception:test
```

### Issue: Rate limits triggering too fast

**Adjust configuration:**
```php
// Increase rate limit
'max_emails_per_signature_per_hour' => 20,  // Default: 10

// Increase time window
'rate_limit_window' => 7200,  // 2 hours instead of 1
```

## 🔄 Rollback Plan

If you need to rollback to v1.x:

```bash
# 1. Restore code
git checkout main
git branch -D upgrade-exception-notifier-v2

# 2. Downgrade package
composer require damku999/exception-notifier:^1.0

# 3. Restore config
cp config/exception_notifier.php.backup config/exception_notifier.php

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
```

## ✅ Upgrade Checklist

Before marking upgrade complete:

- [ ] PHP 8.2+ verified
- [ ] Laravel 12+ verified
- [ ] Composer dependencies updated
- [ ] Configuration republished and merged
- [ ] Bootstrap/app.php updated
- [ ] Environment variables updated
- [ ] Caches cleared
- [ ] Test email sent successfully
- [ ] Rate limit commands work
- [ ] Tested on staging environment
- [ ] Monitoring set up for production
- [ ] Team informed of new features
- [ ] Documentation updated

## 📚 Additional Resources

- [v2.0 CHANGELOG](CHANGELOG.md)
- [v2.0 Installation Guide](INSTALLATION.md)
- [v2.0 Security Guide](SECURITY.md)
- [Laravel 12 Upgrade Guide](https://laravel.com/docs/12.x/upgrade)
- [PHP 8.2 Migration Guide](https://www.php.net/manual/en/migration82.php)

## 🆘 Support

Need help with upgrade?

- 📖 [Documentation](https://github.com/damku999/exception-notifier/wiki)
- 🐛 [Report Issues](https://github.com/damku999/exception-notifier/issues)
- 💬 [Ask Questions](https://github.com/damku999/exception-notifier/discussions)
- 📧 [Email Support](mailto:darshan@adaptit.co.uk)

---

**Upgrade Guide Version:** 2.0
**From Version:** 1.0.1 → 2.0.0
**Author:** Darshan Baraiya
**Repository:** https://github.com/damku999/exception-notifier
