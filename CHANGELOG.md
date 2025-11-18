# Changelog

All notable changes to `exception-notifier` will be documented in this file.

## [2.0.0] - 2025-11-18

### 🚀 Major Release - Laravel 12+ Support

This is a major version upgrade adding Laravel 12+ support with breaking changes from v1.x.

### Added
- 🚨 Exception email notification system
- 🎯 Per-signature rate limiting (default: 10 emails/hour)
- 🔥 Critical exception bypass for rate limits
- 📊 Rich context data (stack trace, request, user info)
- 🎨 Beautiful responsive HTML email templates
- 🤖 Bot detection and filtering
- 🔧 Artisan commands for management
- 🌍 Environment-aware silent mode
- ⚡ Zero-performance-impact design
- 📝 Comprehensive documentation

### Features

#### Core Functionality
- `ExceptionNotifierService` - Main service with 25+ methods
- `JsonExceptionHandler` - Laravel exception handler integration
- Smart rate limiting using Laravel Cache
- Exception signature generation (Class:File:Line)
- Automatic bot detection
- Configurable ignored exceptions
- Configurable critical exceptions

#### Email Templates
- `exception_notification.blade.php` - Main exception email
- `exception_suppression_notice.blade.php` - Rate limit notification
- Responsive design with inline CSS
- Customizable branding (logo, colors, footer)

#### Artisan Commands
- `exception:rate-limit-status` - View current rate limits
- `exception:clear-rate-limits` - Clear rate limits
- `exception:test` - Send test exception email

#### Configuration
- Environment variable support
- Comprehensive config file
- Fallback recipients
- Bot user agent filtering
- Context data inclusion toggles

### 💥 Breaking Changes from v1.x

- **PHP requirement:** 7.4/8.0 → 8.2+ (minimum PHP 8.2.0 required)
- **Laravel requirement:** 8-11 → 12+ (Laravel 12.0+ only)
- **Bootstrap structure:** Requires new Laravel 12 `bootstrap/app.php` pattern
- **Namespace:** Package now uses modern dependency injection
- **Configuration:** New config structure with additional options

### 🔄 Migration from v1.x to v2.0

**Requirements Check:**
```bash
# Verify PHP version
php -v  # Must be 8.2+

# Verify Laravel version
php artisan --version  # Must be 12.x
```

**Installation:**
```bash
# Update composer.json
"require": {
    "php": "^8.2",
    "damku999/exception-notifier": "^2.0"
}

# Update package
composer update damku999/exception-notifier

# Republish configuration
php artisan vendor:publish --tag="exception-notifier-config" --force

# Update bootstrap/app.php (see UPGRADE.md for details)
```

**Configuration Changes:**
```php
// v1.x used basic configuration
// v2.0 adds new options:
'rate_limit_strategy' => 'per_signature',
'max_emails_per_signature_per_hour' => 10,
'critical_exceptions' => [...],
'ignored_bots' => [...],
'branding' => [...],
```

### Requirements (v2.0)
- PHP 8.2+
- Laravel 12.0+
- Mail configuration (SMTP, Mailgun, SES, etc.)

### Migration from Alliance-Auth
Refactored from Alliance-Auth production system for public use.

**Changes from original:**
- Removed project-specific dependencies
- Made MailService implementation optional
- Added service provider with auto-discovery
- Added comprehensive test coverage
- Added complete documentation

**Backward compatibility:**
Maintains API compatibility with original implementation.

---

## [1.0.1] - 2024-09-12 (Previous Release)

### Features (v1.x)
- Email notifications for exceptions
- Basic configuration (recipients, capture rules)
- Ignored exceptions list
- Bot user agent filtering
- Stack trace in emails

### Support (v1.x)
- PHP 7.4+ / 8.0+
- Laravel 8.*, 9.*, 10.*, 11.*

**Note:** v1.x is now in maintenance mode. Only critical security fixes will be backported.

---

## [Unreleased] - Future Versions

### Planned Features for v2.1.0
- [ ] Database-backed rate limiting option
- [ ] Daily exception summary emails
- [ ] Slack/Discord notification channels
- [ ] Exception grouping and aggregation
- [ ] Web dashboard for exception monitoring
- [ ] Multiple email template themes
- [ ] Timezone-aware timestamps
- [ ] Exception search and filtering
- [ ] Integration with Laravel Telescope
- [ ] Integration with Sentry/Bugsnag

### Planned Improvements
- [ ] Performance optimizations for high-traffic apps
- [ ] Better memory management for large stack traces
- [ ] Configurable email queue handling
- [ ] Improved bot detection with machine learning
- [ ] Support for custom notification channels

---

## Release Notes

### Version 1.0.0 - Initial Release

First stable release of Laravel Exception Notifier with Laravel 12+ support.

**Highlights:**
- Production-tested in enterprise environments
- Handles high-traffic applications reliably
- Zero-downtime exception handling
- Intelligent rate limiting prevents email spam
- Comprehensive context data for debugging

**Production Ready:**
- Proven in production environments
- Supports multi-tenant architectures
- Tested with Laravel 12.14.1
- PHP 8.2+ compatible

---

**Format:** This changelog follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
**Versioning:** This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html)
