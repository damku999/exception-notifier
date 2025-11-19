<?php

declare(strict_types=1);

namespace Damku999\ExceptionNotifier\Tests\Feature;

use Damku999\ExceptionNotifier\Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Test Case 3: Critical Exception Bypass
 *
 * Verifies that critical exceptions bypass rate limiting and always send emails.
 *
 * @author Darshan Baraiya <darshan@adaptit.co.uk>
 */
class CriticalExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable exception notifications with rate limiting
        Config::set('exception_notifier.enabled', true);
        Config::set('exception_notifier.silent_local', false);
        Config::set('exception_notifier.rate_limit_strategy', 'per_signature');
        Config::set('exception_notifier.max_emails_per_signature_per_hour', 10);

        // Configure critical exceptions
        Config::set('exception_notifier.critical_exceptions', [
            HttpException::class,
            \PDOException::class,
            \ErrorException::class,
        ]);

        // Configure test recipients
        Config::set('exception_notifier.fallback_recipients', [
            'admin@example.com',
        ]);

        // Clear cache
        Cache::flush();

        // Fake mail
        Mail::fake();
    }

    /** @test */
    public function critical_exceptions_bypass_rate_limiting(): void
    {
        // Arrange - Critical exception should bypass 10 email limit

        // Act - Trigger critical exception 20 times (double the rate limit)
        for ($i = 1; $i <= 20; $i++) {
            try {
                throw new HttpException(500, "Critical system failure #{$i}");
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert - All 20 should send (no rate limiting applied)
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class,
            20
        );

        // No suppression notice should be sent for critical exceptions
        Mail::assertNotSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionSuppressionNoticeMail::class
        );
    }

    /** @test */
    public function critical_exception_emails_marked_as_critical(): void
    {
        // Arrange
        $criticalException = new HttpException(500, 'Database connection lost');

        // Act
        try {
            throw $criticalException;
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class, function ($mail) {
            $mailData = $mail->data;

            // Verify email is marked as critical
            $this->assertArrayHasKey('is_critical', $mailData);
            $this->assertTrue($mailData['is_critical']);

            // Verify subject contains CRITICAL marker
            $this->assertStringContainsString('[CRITICAL]', $mail->subject);

            return true;
        });
    }

    /** @test */
    public function non_critical_exceptions_respect_rate_limits(): void
    {
        // Arrange
        $nonCriticalException = new \RuntimeException('Regular exception');

        // Act - Trigger non-critical exception 15 times
        for ($i = 1; $i <= 15; $i++) {
            try {
                throw $nonCriticalException;
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert - Only 10 should send (rate limiting applied)
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class,
            10
        );

        // Suppression notice should be sent
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionSuppressionNoticeMail::class,
            1
        );
    }

    /** @test */
    public function critical_and_non_critical_exceptions_handled_independently(): void
    {
        // Arrange
        $criticalEx = new HttpException(500, 'Critical error');
        $normalEx = new \RuntimeException('Normal error');

        // Act - Alternate between critical and normal exceptions 15 times each
        for ($i = 1; $i <= 15; $i++) {
            try {
                throw clone $criticalEx;
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }

            try {
                throw clone $normalEx;
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert
        // Critical: All 15 sent (no rate limit)
        // Normal: Only 10 sent (rate limit applied)
        // Total: 25 exception emails
        Mail::assertSent(
            \Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class,
            25
        );
    }

    /** @test */
    public function pdo_exception_treated_as_critical(): void
    {
        // Arrange
        $pdoException = new \PDOException('SQLSTATE[HY000]: General error');

        // Act
        try {
            throw $pdoException;
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class, function ($mail) {
            $this->assertTrue($mail->data['is_critical']);
            return true;
        });
    }
}
