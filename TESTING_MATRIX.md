# Exception Notifier - Testing Matrix

Complete testing strategy across all Laravel and PHP versions.

## Laravel + PHP Compatibility Matrix

| Laravel Version | Min PHP | Max PHP | Test Status |
|----------------|---------|---------|-------------|
| **Laravel 6.x (LTS)** | 7.2 | 8.0 | ⏳ Pending |
| **Laravel 7.x** | 7.2.5 | 8.0 | ⏳ Pending |
| **Laravel 8.x** | 7.3 | 8.2 | ⏳ Pending |
| **Laravel 9.x** | 8.0 | 8.3 | ⏳ Pending |
| **Laravel 10.x** | 8.1 | 8.3 | ⏳ Pending |
| **Laravel 11.x** | 8.2 | 8.4 | ⏳ Pending |
| **Laravel 12.x** | 8.2 | 8.4 | ⏳ Pending |

## Test Projects Structure

```
testing/
├── laravel-6-php72/
├── laravel-6-php74/
├── laravel-6-php80/
├── laravel-7-php74/
├── laravel-7-php80/
├── laravel-8-php74/
├── laravel-8-php80/
├── laravel-8-php81/
├── laravel-8-php82/
├── laravel-9-php80/
├── laravel-9-php81/
├── laravel-9-php82/
├── laravel-9-php83/
├── laravel-10-php81/
├── laravel-10-php82/
├── laravel-10-php83/
├── laravel-11-php82/
├── laravel-11-php83/
├── laravel-11-php84/
├── laravel-12-php82/
├── laravel-12-php83/
└── laravel-12-php84/
```

## Test Cases

### 1. Installation Test
- [ ] Package installs without errors
- [ ] Dependencies resolve correctly
- [ ] Config publishes successfully
- [ ] Views publish successfully

### 2. Functionality Tests
- [ ] Exception notification sends email
- [ ] Rate limiting works correctly
- [ ] Critical exceptions bypass rate limits
- [ ] Ignored exceptions don't send emails
- [ ] Bot detection works
- [ ] Email templates render correctly

### 3. Artisan Commands
- [ ] `exception:test` - Sends test email
- [ ] `exception:rate-limit-status` - Shows rate limits
- [ ] `exception:clear-rate-limits` - Clears rate limits

### 4. Integration Tests
- [ ] Works with Laravel Mail
- [ ] Works with different cache drivers
- [ ] Works with different queue drivers
- [ ] Exception handler integration works

## Current Testing Focus

**Package:** damku999/exception-notifier v1.0.1
**From Alliance-Auth:** Production-tested with Laravel 12, PHP 8.2

**Testing Goal:** Verify backward compatibility with older Laravel/PHP versions

## Test Execution Plan

1. Create test project for each Laravel version
2. Install current package (v1.0.1)
3. Run automated test suite
4. Document any compatibility issues
5. Fix issues if needed
6. Update compatibility documentation
