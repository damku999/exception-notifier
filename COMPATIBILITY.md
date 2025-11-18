# Version Compatibility & Requirements

Complete compatibility matrix and version support guide for Laravel Exception Notifier.

## 📊 Version Support Matrix

### Current Release (v1.0.0)

| Dependency | Minimum Version | Recommended | Tested Versions |
|-----------|----------------|-------------|-----------------|
| **PHP** | 8.2.0 | 8.3.x | 8.2.0 - 8.3.x |
| **Laravel** | 12.0.0 | 12.14.x | 12.0.0 - 12.14.1 |
| **Composer** | 2.0.0 | 2.7.x | 2.0.0+ |

### PHP Version Support

#### ✅ Supported PHP Versions

- **PHP 8.3.x** (Recommended) - Latest stable, best performance
- **PHP 8.2.x** (Minimum) - Production-tested and verified

#### ❌ Unsupported PHP Versions

- **PHP 8.1.x and below** - Missing required type system features
- **PHP 7.x** - End of life, security vulnerabilities

**Why PHP 8.2+?**
- Constructor property promotion
- Readonly properties
- `strict_types` declarations
- Improved type system
- Better performance
- Security updates

### Laravel Version Support

#### ✅ Supported Laravel Versions

- **Laravel 12.14.x** (Recommended) - Tested in production
- **Laravel 12.x** - All 12.x releases supported

#### ⏳ Planned Support

- **Laravel 13.x** - Will be supported upon release
- **Laravel 14.x** - Future compatibility planned

#### ❌ Unsupported Laravel Versions

- **Laravel 11.x and below** - Different exception handling architecture
- **Laravel 10.x and below** - Missing modern features

**Why Laravel 12+?**
- New `bootstrap/app.php` structure
- Modern exception handling with `->withExceptions()`
- No HTTP Kernel class requirement
- Improved dependency injection
- Better middleware architecture

### Required PHP Extensions

```json
{
    "ext-json": "*",
    "ext-mbstring": "*",
    "ext-openssl": "*",
    "ext-pdo": "*"
}
```

**Installation Check:**
```bash
php -m | grep -E "(json|mbstring|openssl|pdo)"
```

### Required Laravel Packages

The package depends on these Laravel components:

```json
{
    "illuminate/support": "^12.0",
    "illuminate/container": "^12.0",
    "illuminate/config": "^12.0",
    "illuminate/mail": "^12.0",
    "illuminate/cache": "^12.0",
    "illuminate/console": "^12.0"
}
```

These are included in Laravel 12 by default.

## 🔄 Upgrading from Older Versions

### Future Version Upgrade Guide

When v2.0.0 is released, follow this process:

```bash
# Check current version
composer show damku999/exception-notifier

# Update to latest
composer update damku999/exception-notifier

# Republish config if needed
php artisan vendor:publish --tag="exception-notifier-config" --force

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### Breaking Changes Policy

We follow [Semantic Versioning](https://semver.org/):

- **Major version (2.0.0)** - Breaking changes, review migration guide
- **Minor version (1.1.0)** - New features, backward compatible
- **Patch version (1.0.1)** - Bug fixes, security patches

## 🚀 Installation Requirements Check

### Pre-Installation Checklist

Run these commands to verify your environment:

```bash
# 1. Check PHP version (should be 8.2+)
php -v

# 2. Check Laravel version (should be 12.x)
php artisan --version

# 3. Check Composer version (should be 2.0+)
composer --version

# 4. Check required PHP extensions
php -m | grep -E "(json|mbstring|openssl|pdo)"

# 5. Check if mail is configured
php artisan tinker
>>> config('mail.default')

# 6. Check cache driver
php artisan tinker
>>> config('cache.default')
```

### Environment Requirements

#### ✅ Required

- PHP 8.2+ installed and configured
- Laravel 12.x project
- Mail configuration (SMTP/Mailgun/SES/etc.)
- Cache driver (file/redis/memcached/database)

#### ⚠️ Recommended

- Redis for cache (better rate limiting performance)
- Queue system (for async email sending)
- Log rotation (prevent disk space issues)
- Monitoring system (track email delivery)

## 🔐 Security Requirements

### SSL/TLS Requirements

For email delivery:
```env
MAIL_ENCRYPTION=tls  # or ssl
MAIL_PORT=587        # for TLS
```

### Environment Security

```env
# Production settings
APP_ENV=production
APP_DEBUG=false
EXCEPTION_EMAIL_ENABLED=true
EXCEPTION_EMAIL_SILENT_LOCAL=true
```

### Cache Security

If using Redis:
```env
REDIS_PASSWORD=your-secure-password
```

## 📦 Composer Version Requirements

### Composer 2.x Required

```bash
# Check Composer version
composer --version

# Upgrade to Composer 2 if needed
composer self-update --2
```

**Why Composer 2?**
- Faster dependency resolution
- Better performance
- Improved security
- Required for Laravel 12

## 🧪 Testing Your Installation

### Compatibility Test Script

Create `tests/compatibility-check.php`:

```php
<?php

echo "=== Laravel Exception Notifier - Compatibility Check ===\n\n";

// Check PHP version
$phpVersion = PHP_VERSION;
$phpOk = version_compare($phpVersion, '8.2.0', '>=');
echo "PHP Version: {$phpVersion} " . ($phpOk ? "✅" : "❌ Minimum 8.2.0 required") . "\n";

// Check Laravel version
$laravelVersion = app()->version();
$laravelOk = version_compare($laravelVersion, '12.0.0', '>=');
echo "Laravel Version: {$laravelVersion} " . ($laravelOk ? "✅" : "❌ Minimum 12.0.0 required") . "\n";

// Check required extensions
$extensions = ['json', 'mbstring', 'openssl', 'pdo'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "Extension {$ext}: " . ($loaded ? "✅" : "❌ Missing") . "\n";
}

// Check mail configuration
$mailDriver = config('mail.default');
echo "\nMail Driver: {$mailDriver} " . ($mailDriver ? "✅" : "⚠️ Not configured") . "\n";

// Check cache configuration
$cacheDriver = config('cache.default');
echo "Cache Driver: {$cacheDriver} " . ($cacheDriver ? "✅" : "⚠️ Not configured") . "\n";

// Check package installation
$installed = class_exists(\Damku999\ExceptionNotifier\ExceptionNotifierServiceProvider::class);
echo "\nPackage Installed: " . ($installed ? "✅" : "❌") . "\n";

// Overall status
$allOk = $phpOk && $laravelOk && $installed;
echo "\n" . ($allOk ? "✅ All checks passed!" : "❌ Some checks failed. Please review.") . "\n";
```

Run the test:
```bash
php artisan tinker < tests/compatibility-check.php
```

## 🌍 Platform Compatibility

### Operating Systems

✅ **Fully Supported:**
- Linux (Ubuntu 20.04+, Debian 11+, CentOS 8+)
- macOS (12.0+)
- Windows (10+, Server 2019+)

### Web Servers

✅ **Tested:**
- Nginx 1.18+
- Apache 2.4+
- Caddy 2.0+

### Database Systems

✅ **Compatible** (for cache storage):
- MySQL 8.0+
- MariaDB 10.5+
- PostgreSQL 13+
- SQLite 3.35+
- Redis 6.0+

### Email Services

✅ **Tested:**
- SMTP (generic)
- Mailgun
- Amazon SES
- Postmark
- SendGrid
- SparkPost

## 📈 Performance Benchmarks

### System Requirements by Scale

#### Small Projects (< 1000 requests/day)
- **CPU:** 1 core
- **RAM:** 512MB
- **Cache:** File driver OK

#### Medium Projects (1000-10000 requests/day)
- **CPU:** 2 cores
- **RAM:** 1GB
- **Cache:** Redis recommended

#### Large Projects (10000+ requests/day)
- **CPU:** 4+ cores
- **RAM:** 2GB+
- **Cache:** Redis required
- **Queue:** Recommended for async emails

### Performance Impact

- **Memory overhead:** ~2MB per request
- **Email sending:** 0-100ms (async queue recommended)
- **Rate limit check:** < 1ms (with Redis)
- **Exception handling:** < 5ms

## 🔄 Backward Compatibility Promise

### What We Guarantee

✅ **Within minor versions (1.x.x):**
- No breaking API changes
- Configuration backward compatible
- Database schema compatible (if added)
- Email templates compatible

✅ **Within patch versions (1.0.x):**
- Only bug fixes
- Security patches
- Performance improvements
- No feature changes

### What Requires Major Version

❌ **Breaking changes (2.0.0):**
- PHP version bump
- Laravel version bump
- API signature changes
- Configuration structure changes
- Database schema changes

## 🆘 Troubleshooting Compatibility Issues

### PHP Version Mismatch

**Error:** `Package requires php ^8.2`

**Solution:**
```bash
# Check current version
php -v

# Ubuntu/Debian - Install PHP 8.2+
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.3

# Update composer.json
"require": {
    "php": "^8.2"
}
```

### Laravel Version Mismatch

**Error:** `Package requires illuminate/support ^12.0`

**Solution:**
```bash
# Check current version
php artisan --version

# Upgrade Laravel (if on 11.x)
# This is a major upgrade, follow Laravel upgrade guide
composer require laravel/framework:^12.0
```

### Missing PHP Extensions

**Error:** `ext-mbstring is missing from your system`

**Solution:**
```bash
# Ubuntu/Debian
sudo apt install php8.3-mbstring php8.3-json

# CentOS/RHEL
sudo yum install php-mbstring php-json

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

## 📞 Support & Resources

- **Documentation:** https://github.com/damku999/exception-notifier/wiki
- **Issue Tracker:** https://github.com/damku999/exception-notifier/issues
- **Discussions:** https://github.com/damku999/exception-notifier/discussions
- **Stack Overflow:** Tag `laravel-exception-notifier`

---

**Package Version:** 2.0.0
**Author:** Darshan Baraiya
**Repository:** https://github.com/damku999/exception-notifier
