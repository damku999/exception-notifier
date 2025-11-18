# Security Policy & Best Practices

Comprehensive security documentation for Laravel Exception Notifier package.

## 🔐 Security Overview

The Laravel Exception Notifier package is designed with **security-first principles**. This document outlines security measures, best practices, and vulnerability reporting procedures.

## 🛡️ Built-in Security Features

### 1. Fail-Safe Exception Handling

**Problem:** Exception in exception handler = infinite loop + application crash

**Solution:** Multi-layer protection
```php
// Layer 1: Try-catch wrapper
try {
    $this->notifierService->notify($e);
} catch (Throwable $notificationError) {
    // Silently fail, never crash the app
    Log::error('Exception notification failed', [
        'error' => $notificationError->getMessage(),
    ]);
}
```

**Guarantees:**
- ✅ Notification failures never break your application
- ✅ Original exception always logged
- ✅ No infinite loops
- ✅ No cascading failures

### 2. Dependency Injection Security

**Problem:** Manual instantiation can miss dependencies

**Solution:** Laravel service container auto-resolution
```php
// ❌ WRONG - Missing dependencies
return (new JsonExceptionHandler)->handle($e);

// ✅ CORRECT - Auto-resolves dependencies
return app(JsonExceptionHandler::class)->handle($e);
```

**Benefits:**
- ✅ Type-safe dependency injection
- ✅ Automatic dependency resolution
- ✅ No missing constructor parameters
- ✅ Testable architecture

### 3. Rate Limiting Protection

**Problem:** Exception flood = email spam + SMTP ban

**Solution:** Per-signature rate limiting
```php
// Configurable rate limits per unique exception
'max_emails_per_signature_per_hour' => 10,
'rate_limit_window' => 3600, // 1 hour
```

**Protection Against:**
- ✅ Email spam attacks
- ✅ SMTP service bans
- ✅ Denial of Service (DoS)
- ✅ Resource exhaustion

### 4. Bot Detection & Filtering

**Problem:** Bot traffic triggers false alarms

**Solution:** Automatic bot detection
```php
'ignored_bots' => [
    'googlebot', 'bingbot', 'slurp', 'crawler', 'spider',
    'bot', 'facebookexternalhit', 'twitterbot', 'whatsapp',
    'telegram', 'curl', 'wget', // CLI tools
],
```

**Benefits:**
- ✅ Reduces false positives
- ✅ Saves email quota
- ✅ Focuses on real issues
- ✅ Prevents bot-triggered spam

### 5. Sensitive Data Protection

**Problem:** Stack traces may contain passwords, tokens, API keys

**Solution:** Configurable context inclusion
```php
// Control what data is included in emails
'include_request_data' => true,  // URL, method, IP
'include_user_data' => true,     // User ID, email
'include_stack_trace' => true,   // Call stack
'max_stack_trace_depth' => 10,   // Limit depth
```

**Best Practice:**
```php
// Production: Limit sensitive data
'include_request_data' => false,  // Disable request body
'include_stack_trace' => true,    // Keep for debugging
'max_stack_trace_depth' => 5,     // Reduce depth
```

### 6. Environment-Aware Behavior

**Problem:** Development spam vs production monitoring

**Solution:** Silent mode in local environment
```php
// Automatic environment detection
'silent_in_local' => env('EXCEPTION_EMAIL_SILENT_LOCAL', true),
```

**Behavior:**
- **Local:** No emails sent (logs only)
- **Staging:** Emails to dev team
- **Production:** Emails to admins

### 7. Email Recipient Management

**Problem:** Emails sent to wrong addresses or exposed in code

**Solution:** Environment-based configuration
```php
// .env file (never committed)
EXCEPTION_EMAIL_TO=admin@example.com,dev@example.com

// Fallback in config (safe for version control)
'fallback_recipients' => ['fallback@example.com'],
```

**Security Rules:**
- ✅ Never hardcode email addresses
- ✅ Use environment variables
- ✅ Validate email format
- ✅ Support multiple recipients

## 🚨 Security Best Practices

### 1. Environment Configuration

#### ✅ Production Settings (Recommended)
```env
# .env (production)
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

EXCEPTION_EMAIL_ENABLED=true
EXCEPTION_EMAIL_SILENT_LOCAL=true
EXCEPTION_EMAIL_TO=admin@example.com,security@example.com
EXCEPTION_EMAIL_MAX_PER_HOUR=10

MAIL_ENCRYPTION=tls
MAIL_PORT=587
```

#### ❌ Insecure Settings (Avoid)
```env
# ❌ DON'T DO THIS
APP_DEBUG=true                          # Exposes stack traces to users
EXCEPTION_EMAIL_ENABLED=false           # Miss critical errors
EXCEPTION_EMAIL_SILENT_LOCAL=false      # Spam during development
MAIL_ENCRYPTION=null                    # Unencrypted email
```

### 2. Sensitive Data Handling

#### ✅ Sanitize Before Logging
```php
// In your exception handler or custom logic
public function handle(Throwable $e)
{
    // Sanitize exception message
    $sanitized = $this->sanitizeMessage($e->getMessage());

    // Create sanitized exception
    $cleanException = new Exception($sanitized, $e->getCode(), $e);

    // Notify with sanitized version
    app(JsonExceptionHandler::class)->handle($cleanException);
}

private function sanitizeMessage(string $message): string
{
    // Remove passwords, tokens, API keys
    $patterns = [
        '/password["\']?\s*[:=]\s*["\']?[^"\'\s]+/i',
        '/token["\']?\s*[:=]\s*["\']?[^"\'\s]+/i',
        '/api[_-]?key["\']?\s*[:=]\s*["\']?[^"\'\s]+/i',
    ];

    return preg_replace($patterns, '[REDACTED]', $message);
}
```

#### ❌ Never Log These
- Passwords (plain or hashed)
- API keys and secrets
- Credit card numbers
- Social Security Numbers
- Private keys
- Session tokens
- OAuth tokens

### 3. Access Control

#### ✅ Restrict Artisan Commands
```php
// app/Providers/AuthServiceProvider.php
Gate::define('manage-exceptions', function ($user) {
    return $user->isAdmin();
});

// In commands (if you add auth)
if (!Gate::allows('manage-exceptions')) {
    $this->error('Unauthorized');
    return 1;
}
```

#### ✅ Protect Rate Limit Management
```bash
# Only allow admins to clear rate limits
php artisan exception:clear-rate-limits  # Requires admin access
```

### 4. Email Security

#### ✅ Use Authenticated SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-strong-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
```

#### ✅ Use Trusted Email Providers
- ✅ Mailgun (recommended)
- ✅ Amazon SES (recommended)
- ✅ SendGrid (recommended)
- ✅ Postmark (recommended)
- ⚠️ Generic SMTP (ensure TLS)

#### ❌ Insecure Email Configuration
```env
# ❌ DON'T DO THIS
MAIL_ENCRYPTION=null    # Unencrypted email
MAIL_PORT=25            # Unencrypted SMTP
MAIL_USERNAME=null      # No authentication
```

### 5. Cache Security

#### ✅ Secure Redis Configuration
```env
REDIS_PASSWORD=your-strong-random-password
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

#### ✅ Cache Encryption (if storing sensitive data)
```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
        'encrypt' => true,  // Enable encryption
    ],
],
```

### 6. Preventing Information Disclosure

#### ✅ Hide Stack Traces from Users
```php
// In production
APP_DEBUG=false

// Users see generic error, not stack traces
return response()->json([
    'message' => 'An error occurred. Please try again later.',
], 500);
```

#### ✅ Customize Email Template for Production
```blade
{{-- Don't include full stack trace in production emails --}}
@if(app()->environment('production'))
    {{-- Show only first 3 frames --}}
    @foreach(array_slice($data['stack_trace'], 0, 3) as $frame)
        ...
    @endforeach
@else
    {{-- Development: show full trace --}}
    @foreach($data['stack_trace'] as $frame)
        ...
    @endforeach
@endif
```

### 7. Ignored Exceptions Configuration

#### ✅ Ignore Non-Critical Exceptions
```php
// config/exception_notifier.php
'ignored_exceptions' => [
    // User-triggered exceptions (not system errors)
    \Illuminate\Validation\ValidationException::class,
    \Illuminate\Auth\AuthenticationException::class,

    // Expected HTTP exceptions
    \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,

    // Rate limiting (intentional)
    \Illuminate\Http\Exceptions\ThrottleRequestsException::class,
],
```

#### ✅ Mark Critical Exceptions
```php
'critical_exceptions' => [
    // Database issues (always notify)
    \Illuminate\Database\QueryException::class,
    \PDOException::class,

    // Custom critical exceptions
    \App\Exceptions\PaymentFailureException::class,
    \App\Exceptions\DataCorruptionException::class,
],
```

## 🔍 Security Audit Checklist

Before deploying to production, verify:

### Configuration Security
- [ ] `APP_DEBUG=false` in production
- [ ] `EXCEPTION_EMAIL_ENABLED=true` in production
- [ ] `EXCEPTION_EMAIL_SILENT_LOCAL=true` to prevent dev spam
- [ ] Valid `EXCEPTION_EMAIL_TO` addresses
- [ ] Strong `MAIL_PASSWORD` for SMTP
- [ ] `MAIL_ENCRYPTION=tls` enabled
- [ ] Strong `REDIS_PASSWORD` if using Redis

### Code Security
- [ ] No hardcoded credentials
- [ ] No sensitive data in exception messages
- [ ] Stack trace depth limited (`max_stack_trace_depth`)
- [ ] Bot detection enabled
- [ ] Rate limiting configured
- [ ] Ignored exceptions list reviewed

### Email Security
- [ ] Using authenticated SMTP
- [ ] TLS encryption enabled
- [ ] Trusted email provider
- [ ] Email addresses validated
- [ ] No sensitive data in email subject

### Access Control
- [ ] Artisan commands protected (if auth added)
- [ ] Config files not web-accessible
- [ ] `.env` file in `.gitignore`
- [ ] Proper file permissions (644 for files, 755 for dirs)

### Monitoring
- [ ] Log rotation configured
- [ ] Disk space monitoring
- [ ] Email delivery monitoring
- [ ] Rate limit tracking

## 🐛 Vulnerability Reporting

### Reporting Security Issues

**DO NOT** create public GitHub issues for security vulnerabilities.

Instead, email security reports to:
- **Email:** darshan@adaptit.co.uk
- **Subject:** [SECURITY] Laravel Exception Notifier - [Brief Description]

### What to Include

1. **Description:** Clear explanation of the vulnerability
2. **Impact:** What could an attacker do?
3. **Reproduction:** Step-by-step instructions
4. **Proof of Concept:** Code or screenshots (if applicable)
5. **Suggested Fix:** If you have one

### Response Timeline

- **Acknowledgment:** Within 48 hours
- **Initial Assessment:** Within 7 days
- **Fix & Release:** Within 30 days (critical issues: 7 days)
- **Disclosure:** After patch released + 30 days

### Responsible Disclosure

We follow coordinated vulnerability disclosure:

1. You report the issue privately
2. We acknowledge and investigate
3. We develop and test a fix
4. We release a security patch
5. We publish a security advisory
6. You may publish your findings after 30 days

### Security Bounty

While we don't offer a formal bounty program, we:
- Credit you in CHANGELOG and security advisory
- Publicly thank you (if desired)
- Prioritize your issues
- Fast-track fixes for your reports

## 📜 Security Advisories

### How We Notify Users

Security issues are announced via:
1. **GitHub Security Advisories** (primary)
2. **GitHub Releases** (with security tag)
3. **CHANGELOG.md** (security section)
4. **Packagist notifications** (automatic)

### Severity Levels

- **🔴 Critical:** Remote code execution, data breach
- **🟠 High:** Authentication bypass, privilege escalation
- **🟡 Medium:** Denial of service, information disclosure
- **🟢 Low:** Minor information leakage

### Patching Policy

- **Critical/High:** Patch released within 7 days
- **Medium:** Patch released within 30 days
- **Low:** Fixed in next minor release

## 🔄 Security Updates

### Staying Informed

Subscribe to security updates:
```bash
# Watch GitHub repository
# Click "Watch" → "Custom" → "Security alerts"
```

### Automatic Updates

Use Dependabot in your project:
```yaml
# .github/dependabot.yml
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
    open-pull-requests-limit: 10
```

### Manual Updates

Check for security updates:
```bash
composer outdated damku999/exception-notifier
composer update damku999/exception-notifier
```

## 📚 Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/12.x/security)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [Composer Security](https://getcomposer.org/doc/articles/security.md)

## 📞 Security Contact

- **Security Email:** darshan@adaptit.co.uk
- **GitHub Security:** https://github.com/damku999/exception-notifier/security
- **Response Time:** 48 hours

---

**Security Policy Version:** 1.0
**Package Version:** 2.0.0
**Author:** Darshan Baraiya
**Repository:** https://github.com/damku999/exception-notifier
