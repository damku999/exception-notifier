# Testing Guide - Laravel Exception Notifier v2.0.0

**Author:** Darshan Baraiya
**Email:** darshan@adaptit.co.uk
**Package:** damku999/exception-notifier v2.0.0

---

## Test Suite Overview

This package includes comprehensive tests covering all core functionality:

### Test Categories

| Category | Files | Tests | Coverage Goal |
|----------|-------|-------|---------------|
| **Feature Tests** | 5 | 40+ | 80%+ |
| **Unit Tests** | TBD | TBD | 80%+ |
| **Total** | 5+ | 40+ | 80%+ |

---

## Running Tests

### Prerequisites

```bash
# Install dependencies
composer install

# Ensure PHPUnit is available
vendor/bin/phpunit --version
```

### Run All Tests

```bash
# Run complete test suite
composer test

# Or directly with PHPUnit
vendor/bin/phpunit

# Run with coverage report
vendor/bin/phpunit --coverage-html .coverage/html
```

### Run Specific Test Suites

```bash
# Feature tests only
vendor/bin/phpunit --testsuite Feature

# Unit tests only
vendor/bin/phpunit --testsuite Unit

# Specific test file
vendor/bin/phpunit tests/Feature/ExceptionEmailTest.php

# Specific test method
vendor/bin/phpunit --filter it_sends_exception_email_when_exception_occurs
```

---

## The 5 Minimum Test Cases

### Test Case 1: Basic Exception Email Sending

**File:** `tests/Feature/ExceptionEmailTest.php`
**Tests:** 5 tests

| Test Name | Purpose |
|-----------|---------|
| `it_sends_exception_email_when_exception_occurs` | Verify exception triggers email |
| `exception_email_contains_required_details` | Verify email has all exception data |
| `exception_email_includes_request_context` | Verify email has request context |
| `no_email_sent_when_notifications_disabled` | Verify disabled flag works |
| `no_email_sent_in_local_environment_when_silent_mode_enabled` | Verify silent mode works |

**Run:**
```bash
vendor/bin/phpunit tests/Feature/ExceptionEmailTest.php
```

**Expected Output:**
```
OK (5 tests, 15 assertions)
```

---

### Test Case 2: Rate Limiting Functionality

**File:** `tests/Feature/RateLimitingTest.php`
**Tests:** 5 tests

| Test Name | Purpose |
|-----------|---------|
| `rate_limiting_allows_first_ten_exceptions` | Verify first 10 emails sent |
| `rate_limiting_suppresses_emails_after_limit_reached` | Verify suppression after limit |
| `different_exceptions_have_separate_rate_limits` | Verify per-signature tracking |
| `rate_limit_status_command_shows_active_limits` | Verify status command |
| `clear_rate_limits_command_resets_all_limits` | Verify clear command |

**Run:**
```bash
vendor/bin/phpunit tests/Feature/RateLimitingTest.php
```

**Expected Output:**
```
OK (5 tests, 12 assertions)
```

---

### Test Case 3: Critical Exception Bypass

**File:** `tests/Feature/CriticalExceptionTest.php`
**Tests:** 5 tests

| Test Name | Purpose |
|-----------|---------|
| `critical_exceptions_bypass_rate_limiting` | Verify bypass works |
| `critical_exception_emails_marked_as_critical` | Verify CRITICAL flag |
| `non_critical_exceptions_respect_rate_limits` | Verify normal rate limiting |
| `critical_and_non_critical_exceptions_handled_independently` | Verify independent tracking |
| `pdo_exception_treated_as_critical` | Verify PDO exceptions critical |

**Run:**
```bash
vendor/bin/phpunit tests/Feature/CriticalExceptionTest.php
```

**Expected Output:**
```
OK (5 tests, 10 assertions)
```

---

### Test Case 4: Ignored Exceptions

**File:** `tests/Feature/IgnoredExceptionsTest.php`
**Tests:** 8 tests

| Test Name | Purpose |
|-----------|---------|
| `ignored_exception_does_not_send_email` | Verify ignored exceptions filtered |
| `non_ignored_exception_sends_email` | Verify non-ignored send |
| `throttle_exception_is_ignored` | Verify throttle exceptions ignored |
| `child_class_of_ignored_exception_is_also_ignored` | Verify inheritance handling |
| `multiple_ignored_exceptions_handled_correctly` | Verify multiple ignore |
| `ignored_exceptions_are_still_logged` | Verify logging still works |
| `empty_ignored_list_sends_all_exceptions` | Verify default behavior |
| `ignored_exceptions_can_be_added_at_runtime` | Verify runtime config |

**Run:**
```bash
vendor/bin/phpunit tests/Feature/IgnoredExceptionsTest.php
```

**Expected Output:**
```
OK (8 tests, 16 assertions)
```

---

### Test Case 5: Bot Detection Filtering

**File:** `tests/Feature/BotDetectionTest.php`
**Tests:** 9 tests

| Test Name | Purpose |
|-----------|---------|
| `googlebot_requests_do_not_send_exception_emails` | Verify Googlebot filtered |
| `bingbot_requests_are_filtered` | Verify Bingbot filtered |
| `normal_user_requests_send_exception_emails` | Verify normal users send |
| `case_insensitive_bot_detection` | Verify case insensitive |
| `partial_bot_name_match_is_detected` | Verify partial matching |
| `social_media_bots_are_filtered` | Verify social bots |
| `bot_detection_logs_filtered_requests` | Verify logging |
| `empty_user_agent_sends_notification` | Verify empty UA sends |
| `bot_list_can_be_updated_at_runtime` | Verify runtime config |

**Run:**
```bash
vendor/bin/phpunit tests/Feature/BotDetectionTest.php
```

**Expected Output:**
```
OK (9 tests, 18 assertions)
```

---

## Test Coverage

### Generate Coverage Report

```bash
# HTML coverage report
vendor/bin/phpunit --coverage-html .coverage/html

# Open in browser
open .coverage/html/index.html

# Text coverage summary
vendor/bin/phpunit --coverage-text

# Clover XML (for CI/CD)
vendor/bin/phpunit --coverage-clover .coverage/clover.xml
```

### Coverage Goals

| Component | Target | Status |
|-----------|--------|--------|
| Services | 90%+ | ⏳ Pending source code |
| Exception Handlers | 85%+ | ⏳ Pending source code |
| Commands | 80%+ | ⏳ Pending source code |
| Mail Classes | 75%+ | ⏳ Pending source code |
| **Overall** | **80%+** | ⏳ Pending source code |

---

## Testing with Different PHP Versions

### Using WAMP PHP Versions

```bash
# PHP 7.4 (should fail - not supported)
C:\wamp64\bin\php\php7.4.33\php.exe vendor/bin/phpunit
# Expected: PHP version error

# PHP 8.0 (should fail - not supported)
C:\wamp64\bin\php\php8.0.30\php.exe vendor/bin/phpunit
# Expected: PHP version error

# PHP 8.1 (should fail - not supported)
C:\wamp64\bin\php\php8.1.31\php.exe vendor/bin/phpunit
# Expected: PHP version error

# PHP 8.2 (SUPPORTED)
C:\wamp64\bin\php\php8.2.26\php.exe vendor/bin/phpunit
# Expected: All tests pass

# PHP 8.3 (SUPPORTED)
C:\wamp64\bin\php\php8.3.14\php.exe vendor/bin/phpunit
# Expected: All tests pass

# PHP 8.4 (Untested - may work)
C:\wamp64\bin\php\php8.4.0\php.exe vendor/bin/phpunit
# Expected: Likely to work
```

---

## Testing with Different Laravel Versions

### Laravel 12 (SUPPORTED)

```bash
cd C:/xampp/htdocs/oakrays/exception-notifier-tests/laravel-12-test

# Install package
composer require damku999/exception-notifier:dev-v2.0-laravel-12-docs

# Run tests
vendor/bin/phpunit
```

**Expected:** All tests pass

---

### Laravel 11 (UNSUPPORTED - Should Reject)

```bash
cd C:/xampp/htdocs/oakrays/exception-notifier-tests/laravel-11-test

# Attempt to install package
composer require damku999/exception-notifier:dev-v2.0-laravel-12-docs
```

**Expected Error:**
```
Problem 1
  - damku999/exception-notifier v2.0.0 requires illuminate/support ^12.0
  - laravel/framework v11.x requires illuminate/support ^11.0
  - Conclusion: don't install damku999/exception-notifier v2.0.0
```

---

### Laravel 10 (UNSUPPORTED - Should Reject)

```bash
cd C:/xampp/htdocs/oakrays/exception-notifier-tests/laravel-10-test

# Attempt to install package
composer require damku999/exception-notifier:dev-v2.0-laravel-12-docs
```

**Expected:** Same rejection error as Laravel 11

---

### Laravel 9 (UNSUPPORTED - Should Reject)

```bash
cd C:/xampp/htdocs/oakrays/exception-notifier-tests/laravel-9-test

# Attempt to install package
composer require damku999/exception-notifier:dev-v2.0-laravel-12-docs
```

**Expected:** Same rejection error as Laravel 11

---

## Continuous Integration (CI/CD)

### GitHub Actions Workflow

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: [8.2, 8.3]
        laravel: [12.*]
        include:
          - php: 8.2
            laravel: 12.*
          - php: 8.3
            laravel: 12.*

    name: PHP ${{ matrix.php }} - Laravel ${{ matrix.laravel }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: dom, curl, libxml, mbstring, zip
          coverage: xdebug

      - name: Install dependencies
        run: |
          composer require "laravel/framework:${{ matrix.laravel }}" --no-update
          composer update --prefer-dist --no-interaction

      - name: Run tests
        run: vendor/bin/phpunit --coverage-clover coverage.xml

      - name: Upload coverage
        uses: codecov/codecov-action@v4
        with:
          file: ./coverage.xml
```

---

## Test Environment Variables

### Environment Configuration

Tests use these environment variables (from `phpunit.xml`):

```env
APP_ENV=testing
APP_DEBUG=true
CACHE_DRIVER=array
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
MAIL_MAILER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
EXCEPTION_EMAIL_ENABLED=true
EXCEPTION_EMAIL_SILENT_LOCAL=false
```

### Override in Tests

```php
// Temporarily override config in test
Config::set('exception_notifier.enabled', false);

// Restore after test (or use setUp/tearDown)
```

---

## Troubleshooting Tests

### Common Issues

**Issue 1: Tests fail with "Class not found"**

```bash
# Solution: Regenerate autoloader
composer dump-autoload
```

**Issue 2: Mail assertions fail**

```php
// Ensure Mail::fake() is called BEFORE triggering exception
Mail::fake();

// Then trigger exception
throw new \Exception('test');

// Then assert
Mail::assertSent(...);
```

**Issue 3: Rate limiting tests interfere with each other**

```php
// Solution: Clear cache in setUp()
protected function setUp(): void
{
    parent::setUp();
    Cache::flush();
}
```

---

## Writing New Tests

### Test Template

```php
<?php

declare(strict_types=1);

namespace Damku999\ExceptionNotifier\Tests\Feature;

use Damku999\ExceptionNotifier\Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class MyNewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test-specific setup
        Mail::fake();
    }

    /** @test */
    public function it_does_something(): void
    {
        // Arrange
        $exception = new \Exception('test');

        // Act
        $handler = $this->getExceptionHandler();
        $handler->report($exception);

        // Assert
        Mail::assertSent(/* ... */);
    }
}
```

---

## Test Metrics

### Current Status (v2.0.0)

| Metric | Value | Status |
|--------|-------|--------|
| **Total Tests** | 32 | ✅ Complete |
| **Feature Tests** | 32 | ✅ Complete |
| **Unit Tests** | 0 | ⏳ Pending |
| **Coverage** | TBD | ⏳ Pending source code |
| **Pass Rate** | TBD | ⏳ Pending source code |

### Test Execution Time

Expected test execution time: **< 10 seconds** for full suite

```bash
# Measure execution time
time vendor/bin/phpunit
```

---

## Next Steps

1. **Extract source code** from Alliance-Auth
2. **Update namespaces** (App → Damku999\ExceptionNotifier)
3. **Run tests** to verify functionality
4. **Achieve 80%+ coverage**
5. **Fix any failing tests**
6. **Add unit tests** for individual methods
7. **Document test results**

---

**Author:** Darshan Baraiya
**Email:** darshan@adaptit.co.uk
**Package Repository:** https://github.com/damku999/exception-notifier
