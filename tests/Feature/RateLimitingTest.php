<?php

declare(strict_types=1);

namespace Damku999\ExceptionNotifier\Tests\Feature;

use Damku999\ExceptionNotifier\Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

/**
 * Test Case 2: Rate Limiting Functionality
 *
 * Verifies per-signature rate limiting prevents email spam while allowing
 * the first N occurrences of each unique exception.
 *
 * @author Darshan Baraiya <darshan@adaptit.co.uk>
 */
class RateLimitingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable exception notifications with rate limiting
        Config::set('exception_notifier.enabled', true);
        Config::set('exception_notifier.silent_local', false);
        Config::set('exception_notifier.rate_limit_strategy', 'per_signature');
        Config::set('exception_notifier.max_emails_per_signature_per_hour', 10);

        // Configure test recipients
        Config::set('exception_notifier.fallback_recipients', [
            'test@example.com',
        ]);

        // Clear cache before each test
        Cache::flush();

        // Fake mail
        Mail::fake();
    }

    /** @test */
    public function rate_limiting_allows_first_ten_exceptions(): void
    {
        // Arrange
        $exceptionMessage = 'Rate limit test exception';

        // Act - Trigger same exception 10 times
        for ($i = 1; $i <= 10; $i++) {
            try {
                throw new \RuntimeException($exceptionMessage, 999);
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class,
            10
        );
    }

    /** @test */
    public function rate_limiting_suppresses_emails_after_limit_reached(): void
    {
        // Arrange
        $exceptionMessage = 'Rate limit suppression test';

        // Act - Trigger same exception 15 times (5 over limit)
        for ($i = 1; $i <= 15; $i++) {
            try {
                throw new \RuntimeException($exceptionMessage, 777);
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class,
            10  // Only first 10 should send
        );

        // Suppression notice should be sent once when limit first exceeded
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionSuppressionNoticeMail::class,
            1
        );
    }

    /** @test */
    public function different_exceptions_have_separate_rate_limits(): void
    {
        // Arrange
        $exception1Message = 'First exception type';
        $exception2Message = 'Second exception type';

        // Act - Trigger two different exceptions 10 times each
        for ($i = 1; $i <= 10; $i++) {
            try {
                throw new \RuntimeException($exception1Message, 100);
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }

            try {
                throw new \LogicException($exception2Message, 200);
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert - Both exception types should send all 10 emails (20 total)
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class,
            20
        );
    }

    /** @test */
    public function rate_limit_status_command_shows_active_limits(): void
    {
        // Arrange
        $exceptionMessage = 'Rate limit status test';

        // Act - Trigger exception 5 times
        for ($i = 1; $i <= 5; $i++) {
            try {
                throw new \RuntimeException($exceptionMessage, 555);
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert - Verify rate limit cache key exists
        $signatureHash = $this->generateExceptionSignature(
            \RuntimeException::class,
            __FILE__,
            __LINE__
        );

        $cacheKey = "exception_rate_limit:{$signatureHash}";
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals(5, Cache::get($cacheKey));
    }

    /** @test */
    public function clear_rate_limits_command_resets_all_limits(): void
    {
        // Arrange
        $exceptionMessage = 'Clear rate limit test';

        // Trigger exception 10 times to hit limit
        for ($i = 1; $i <= 10; $i++) {
            try {
                throw new \RuntimeException($exceptionMessage, 666);
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Act - Clear rate limits
        $this->artisan('exception:clear-rate-limits')->assertExitCode(0);

        // Assert - Should be able to send emails again
        Mail::fake();  // Reset mail fake

        try {
            throw new \RuntimeException($exceptionMessage, 666);
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class);
    }

    /**
     * Generate exception signature (Class:File:Line)
     */
    private function generateExceptionSignature(string $class, string $file, int $line): string
    {
        return md5("{$class}:{$file}:{$line}");
    }
}
