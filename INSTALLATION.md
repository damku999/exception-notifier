# Installation Guide

Complete step-by-step installation guide for Laravel Exception Notifier.

## 📋 Prerequisites

Before installing, ensure you meet these requirements:

### System Requirements
- ✅ **PHP:** 8.2 or higher
- ✅ **Laravel:** 12.0 or higher
- ✅ **Composer:** 2.0 or higher
- ✅ **Mail:** Configured mail driver (SMTP, Mailgun, SES, etc.)
- ✅ **Cache:** Any cache driver (file, Redis, Memcached, etc.)

### Verify Requirements

Run these commands to check:

```bash
# Check PHP version
php -v  # Should show 8.2.0 or higher

# Check Laravel version
php artisan --version  # Should show 12.x

# Check Composer version
composer --version  # Should show 2.x

# Check PHP extensions
php -m | grep -E "(json|mbstring|openssl)"
```

## 🚀 Installation Steps

### Step 1: Install Package via Composer

```bash
composer require damku999/exception-notifier
```

**Output:**
```
Using version ^1.0 for damku999/exception-notifier
./composer.json has been updated
Running composer update damku999/exception-notifier
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Locking damku999/exception-notifier (1.0.0)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
  - Downloading damku999/exception-notifier (1.0.0)
  - Installing damku999/exception-notifier (1.0.0): Extracting archive
Package manifest generated successfully.
Discovered Package: damku999/exception-notifier
```

### Step 2: Publish Configuration File

```bash
php artisan vendor:publish --tag="exception-notifier-config"
```

**Output:**
```
Copied File [/vendor/damku999/exception-notifier/src/Config/exception_notifier.php]
         To [/config/exception_notifier.php]
Publishing complete.
```

This creates `config/exception_notifier.php` with all configuration options.

### Step 3: Configure Environment Variables

Add these to your `.env` file:

```env
# Enable exception email notifications
EXCEPTION_EMAIL_ENABLED=true

# Silent mode in local environment (recommended)
EXCEPTION_EMAIL_SILENT_LOCAL=true

# Email recipients (comma-separated for multiple)
EXCEPTION_EMAIL_TO=admin@example.com,dev@example.com

# Rate limiting (optional, defaults shown)
EXCEPTION_EMAIL_MAX_PER_HOUR=10
EXCEPTION_EMAIL_RATE_WINDOW=3600
```

### Step 4: Configure Mail Settings

Ensure your mail configuration is set in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 5: Update Exception Handler

Edit `bootstrap/app.php` and add the exception handler:

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
        // Add exception notifier handler
        $exceptions->render(function (Throwable $e) {
            return app(JsonExceptionHandler::class)->handle($e);
        });
    })->create();
```

### Step 6: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 7: Test Installation

Send a test exception email:

```bash
php artisan exception:test
```

**Expected Output:**
```
Testing exception notification system...

✅ Test exception email sent successfully!

Check your email at: admin@example.com

If you don't receive the email within 5 minutes:
1. Check spam/junk folder
2. Verify mail configuration in .env
3. Check Laravel log for mail errors
```

## 📧 Verify Email Delivery

### Check Mail Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Look for mail-related entries
grep -i "mail" storage/logs/laravel.log
```

### Test Mail Configuration

```bash
php artisan tinker

# Inside Tinker
Mail::raw('Test email', function($message) {
    $message->to('admin@example.com')
            ->subject('Test Email');
});

# Check output for errors
```

## 🎨 Optional: Customize Email Templates

If you want to customize the email appearance:

### Publish Email Views

```bash
php artisan vendor:publish --tag="exception-notifier-views"
```

**Output:**
```
Copied Directory [/vendor/damku999/exception-notifier/resources/views]
              To [/resources/views/vendor/exception-notifier]
Publishing complete.
```

### Customize Templates

Edit these files:
- `resources/views/vendor/exception-notifier/exception_notification.blade.php`
- `resources/views/vendor/exception-notifier/exception_suppression_notice.blade.php`

## ⚙️ Advanced Configuration

### Customize Ignored Exceptions

Edit `config/exception_notifier.php`:

```php
'ignored_exceptions' => [
    // Add your custom exceptions to ignore
    \Illuminate\Validation\ValidationException::class,
    \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    \App\Exceptions\CustomIgnoredException::class,  // Your custom exception
],
```

### Customize Critical Exceptions

```php
'critical_exceptions' => [
    // Add exceptions that should always send emails (bypass rate limits)
    \Illuminate\Database\QueryException::class,
    \App\Exceptions\PaymentFailureException::class,  // Your critical exception
],
```

### Customize Email Branding

Edit `config/exception_notifier.php`:

```php
'branding' => [
    'email_logo' => 'images/your-logo.png',  // Path to logo in public/
    'primary_color' => '#007bff',            // Your brand color
    'text_color' => '#333333',
    'footer_text' => 'Your Company Name',
    'support_email' => 'support@example.com',
],
```

### Configure Bot Detection

```php
'ignored_bots' => [
    'googlebot',
    'bingbot',
    'your-custom-bot',  // Add custom bots to ignore
],
```

## 🔧 Environment-Specific Setup

### Local Development

```env
# .env.local
APP_ENV=local
APP_DEBUG=true
EXCEPTION_EMAIL_ENABLED=false         # Disable emails
EXCEPTION_EMAIL_SILENT_LOCAL=true
```

### Staging Environment

```env
# .env.staging
APP_ENV=staging
APP_DEBUG=false
EXCEPTION_EMAIL_ENABLED=true
EXCEPTION_EMAIL_SILENT_LOCAL=false
EXCEPTION_EMAIL_TO=dev-team@example.com
EXCEPTION_EMAIL_MAX_PER_HOUR=20       # Higher limit for testing
```

### Production Environment

```env
# .env.production
APP_ENV=production
APP_DEBUG=false
EXCEPTION_EMAIL_ENABLED=true
EXCEPTION_EMAIL_SILENT_LOCAL=true
EXCEPTION_EMAIL_TO=admin@example.com,security@example.com
EXCEPTION_EMAIL_MAX_PER_HOUR=10
```

## 🐳 Docker Installation

### Docker Compose Configuration

Add mail service to `docker-compose.yml`:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      MAIL_MAILER: smtp
      MAIL_HOST: mailhog
      MAIL_PORT: 1025
      EXCEPTION_EMAIL_ENABLED: true

  mailhog:  # For local testing
    image: mailhog/mailhog:latest
    ports:
      - "1025:1025"  # SMTP
      - "8025:8025"  # Web UI
```

### Dockerfile

Ensure PHP extensions are installed:

```dockerfile
FROM php:8.3-fpm

# Install required extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . /var/www/html
WORKDIR /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache
```

## 📊 Performance Optimization

### Use Redis for Cache (Recommended)

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your-secure-password
REDIS_PORT=6379
```

### Enable Queue for Async Emails

```env
QUEUE_CONNECTION=redis

# In config/exception_notifier.php
'use_queue' => true,  // Send emails asynchronously
```

Start queue worker:
```bash
php artisan queue:work --tries=3
```

### Configure Log Rotation

Prevent log files from growing too large:

```bash
# Linux - Add to crontab
0 0 * * * cd /path/to/project && php artisan log:clear --days=7
```

## 🧪 Verify Installation

### Run Full Verification

Create a test script `tests/verify-installation.php`:

```php
<?php

echo "=== Exception Notifier Installation Verification ===\n\n";

// 1. Check package installation
$installed = class_exists(\Damku999\ExceptionNotifier\ExceptionNotifierServiceProvider::class);
echo "1. Package Installed: " . ($installed ? "✅" : "❌ Run: composer require damku999/exception-notifier") . "\n";

// 2. Check config file
$configExists = file_exists(config_path('exception_notifier.php'));
echo "2. Config Published: " . ($configExists ? "✅" : "❌ Run: php artisan vendor:publish --tag=exception-notifier-config") . "\n";

// 3. Check mail configuration
$mailConfigured = config('mail.default') !== null;
echo "3. Mail Configured: " . ($mailConfigured ? "✅" : "❌ Configure mail in .env") . "\n";

// 4. Check cache configuration
$cacheConfigured = config('cache.default') !== null;
echo "4. Cache Configured: " . ($cacheConfigured ? "✅" : "❌ Configure cache in .env") . "\n";

// 5. Check email recipients
$recipients = config('exception_notifier.recipients');
$recipientsOk = !empty($recipients);
echo "5. Recipients Set: " . ($recipientsOk ? "✅" : "⚠️ Set EXCEPTION_EMAIL_TO in .env") . "\n";

// 6. Check if enabled
$enabled = config('exception_notifier.enabled');
echo "6. Notifier Enabled: " . ($enabled ? "✅" : "⚠️ Set EXCEPTION_EMAIL_ENABLED=true in .env") . "\n";

// 7. Test exception handler
$handlerWorks = false;
try {
    $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
    $handlerWorks = $handler !== null;
} catch (\Throwable $e) {
    // Handler not registered
}
echo "7. Exception Handler: " . ($handlerWorks ? "✅" : "❌ Update bootstrap/app.php") . "\n";

// Overall status
$allOk = $installed && $configExists && $mailConfigured && $cacheConfigured && $handlerWorks;
echo "\n" . ($allOk ? "✅ Installation verified successfully!" : "❌ Some issues detected. Please review.") . "\n";
echo "\nNext steps:\n";
echo "  - Send test email: php artisan exception:test\n";
echo "  - Check rate limits: php artisan exception:rate-limit-status\n";
echo "  - Review config: config/exception_notifier.php\n";
```

Run verification:
```bash
php artisan tinker < tests/verify-installation.php
```

## 🆘 Troubleshooting

### Issue: Package Not Found

```bash
# Error: Package damku999/exception-notifier not found
```

**Solution:**
```bash
# Update Composer
composer self-update

# Clear Composer cache
composer clear-cache

# Try again
composer require damku999/exception-notifier
```

### Issue: Config Not Publishing

```bash
# Error: Nothing to publish for tag [exception-notifier-config]
```

**Solution:**
```bash
# Clear config cache
php artisan config:clear

# Try publishing again
php artisan vendor:publish --tag="exception-notifier-config" --force
```

### Issue: Emails Not Sending

**Check mail configuration:**
```bash
php artisan tinker

# Test basic mail
Mail::raw('Test', function($m) {
    $m->to('test@example.com')->subject('Test');
});

# Check for errors in output
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log | grep -i "mail"
```

### Issue: Rate Limits Not Working

**Check cache driver:**
```bash
php artisan tinker

# Test cache
Cache::put('test', 'value', 60);
Cache::get('test');  // Should return 'value'
```

**If using Redis:**
```bash
# Test Redis connection
redis-cli ping  # Should return PONG
```

## 📚 Next Steps

After successful installation:

1. ✅ **Test Exception Emails**
   ```bash
   php artisan exception:test
   ```

2. ✅ **Review Configuration**
   ```bash
   cat config/exception_notifier.php
   ```

3. ✅ **Monitor Rate Limits**
   ```bash
   php artisan exception:rate-limit-status
   ```

4. ✅ **Read Documentation**
   - [Usage Guide](docs/usage.md)
   - [Customization Guide](docs/customization.md)
   - [Security Guide](SECURITY.md)

5. ✅ **Set Up Monitoring**
   - Configure log rotation
   - Set up email delivery monitoring
   - Enable queue workers (if using queues)

## 🔄 Upgrading

When new versions are released:

```bash
# Update to latest version
composer update damku999/exception-notifier

# Republish config (if needed)
php artisan vendor:publish --tag="exception-notifier-config" --force

# Clear caches
php artisan config:clear
php artisan cache:clear

# Test installation
php artisan exception:test
```

## 📞 Support

Need help with installation?

- 📖 [Full Documentation](https://github.com/damku999/exception-notifier/wiki)
- 🐛 [Report Issues](https://github.com/damku999/exception-notifier/issues)
- 💬 [Ask Questions](https://github.com/damku999/exception-notifier/discussions)

---

**Package Version:** 2.0.0
**Author:** Darshan Baraiya
**Repository:** https://github.com/damku999/exception-notifier
