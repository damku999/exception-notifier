<?php

declare(strict_types=1);

namespace Damku999\ExceptionNotifier\Tests\Feature;

use Damku999\ExceptionNotifier\Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

/**
 * Test Case 4: Ignored Exceptions
 *
 * Verifies that configured ignored exceptions don't send notification emails.
 *
 * @author Darshan Baraiya <darshan@adaptit.co.uk>
 */
class IgnoredExceptionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable exception notifications
        Config::set('exception_notifier.enabled', true);
        Config::set('exception_notifier.silent_local', false);

        // Configure ignored exceptions
        Config::set('exception_notifier.ignored_exceptions', [
            NotFoundHttpException::class,
            ThrottleRequestsException::class,
            HttpException::class,  // Ignore all HTTP exceptions by base class
        ]);

        // Configure test recipients
        Config::set('exception_notifier.fallback_recipients', [
            'test@example.com',
        ]);

        // Fake mail
        Mail::fake();
    }

    /** @test */
    public function ignored_exception_does_not_send_email(): void
    {
        // Act - Trigger ignored exception (404 Not Found)
        try {
            throw new NotFoundHttpException('Page not found');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();
    }

    /** @test */
    public function non_ignored_exception_sends_email(): void
    {
        // Act - Trigger non-ignored exception
        try {
            throw new \RuntimeException('This should send email');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class);
    }

    /** @test */
    public function throttle_exception_is_ignored(): void
    {
        // Act - Trigger throttle exception (rate limiting)
        try {
            throw new ThrottleRequestsException('Too many requests');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();
    }

    /** @test */
    public function child_class_of_ignored_exception_is_also_ignored(): void
    {
        // Arrange - HttpException is ignored, test child class
        $childException = new NotFoundHttpException('Not found');  // Extends HttpException

        // Act
        try {
            throw $childException;
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert - Child class should also be ignored
        Mail::assertNothingSent();
    }

    /** @test */
    public function multiple_ignored_exceptions_handled_correctly(): void
    {
        // Act - Trigger multiple ignored exceptions
        $ignoredExceptions = [
            new NotFoundHttpException('404 error'),
            new ThrottleRequestsException('Rate limited'),
            new HttpException(400, 'Bad request'),
        ];

        foreach ($ignoredExceptions as $exception) {
            try {
                throw $exception;
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }
        }

        // Assert - No emails sent for any ignored exception
        Mail::assertNothingSent();
    }

    /** @test */
    public function ignored_exceptions_are_still_logged(): void
    {
        // Arrange
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            unlink($logPath);  // Clear log
        }

        // Act - Trigger ignored exception
        try {
            throw new NotFoundHttpException('Test 404 for logging');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();  // No email sent

        // Exception should still be logged to Laravel log
        $this->assertTrue(file_exists($logPath));
        $logContents = file_get_contents($logPath);
        $this->assertStringContainsString('NotFoundHttpException', $logContents);
        $this->assertStringContainsString('Test 404 for logging', $logContents);
    }

    /** @test */
    public function empty_ignored_list_sends_all_exceptions(): void
    {
        // Arrange - Clear ignored exceptions list
        Config::set('exception_notifier.ignored_exceptions', []);

        // Act - Trigger previously ignored exception
        try {
            throw new NotFoundHttpException('Should send now');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert - Email should be sent when ignore list is empty
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class);
    }

    /** @test */
    public function ignored_exceptions_can_be_added_at_runtime(): void
    {
        // Arrange - Start with empty ignore list
        Config::set('exception_notifier.ignored_exceptions', []);

        // First exception should send
        try {
            throw new \InvalidArgumentException('First attempt');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class);

        // Act - Add exception to ignore list at runtime
        Mail::fake();  // Reset mail fake
        Config::set('exception_notifier.ignored_exceptions', [
            \InvalidArgumentException::class,
        ]);

        // Second exception should NOT send
        try {
            throw new \InvalidArgumentException('Second attempt');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();
    }
}
