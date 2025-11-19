<?php

declare(strict_types=1);

namespace Damku999\ExceptionNotifier\Tests\Feature;

use Damku999\ExceptionNotifier\Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

/**
 * Test Case 1: Basic Exception Email Sending
 *
 * Verifies that exception emails are sent successfully when an exception occurs.
 *
 * @author Darshan Baraiya <darshan@adaptit.co.uk>
 */
class ExceptionEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable exception notifications
        Config::set('exception_notifier.enabled', true);
        Config::set('exception_notifier.silent_local', false);

        // Configure test recipients
        Config::set('exception_notifier.fallback_recipients', [
            'test@example.com',
        ]);

        // Fake mail to prevent actual sending
        Mail::fake();
    }

    /** @test */
    public function it_sends_exception_email_when_exception_occurs(): void
    {
        // Arrange
        $testException = new \RuntimeException('Test exception message', 500);

        // Act - Trigger exception
        try {
            throw $testException;
        } catch (\Exception $e) {
            // Exception should be caught by JsonExceptionHandler
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class, function ($mail) {
            // Verify recipient
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function exception_email_contains_required_details(): void
    {
        // Arrange
        $testException = new \RuntimeException('Detailed test exception', 12345);

        // Act
        try {
            throw $testException;
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class, function ($mail) use ($testException) {
            $mailData = $mail->data;

            // Verify email contains exception details
            $this->assertArrayHasKey('exception_class', $mailData);
            $this->assertArrayHasKey('exception_message', $mailData);
            $this->assertArrayHasKey('exception_file', $mailData);
            $this->assertArrayHasKey('exception_line', $mailData);
            $this->assertArrayHasKey('stack_trace', $mailData);

            // Verify specific values
            $this->assertStringContainsString('RuntimeException', $mailData['exception_class']);
            $this->assertEquals('Detailed test exception', $mailData['exception_message']);
            $this->assertEquals(12345, $mailData['exception_code']);

            return true;
        });
    }

    /** @test */
    public function exception_email_includes_request_context(): void
    {
        // Arrange
        $request = $this->createTestRequest();

        // Act
        try {
            throw new \Exception('Exception with request context');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class, function ($mail) {
            $mailData = $mail->data;

            // Verify request context
            $this->assertArrayHasKey('request_url', $mailData);
            $this->assertArrayHasKey('request_method', $mailData);
            $this->assertArrayHasKey('request_ip', $mailData);

            return true;
        });
    }

    /** @test */
    public function no_email_sent_when_notifications_disabled(): void
    {
        // Arrange
        Config::set('exception_notifier.enabled', false);

        // Act
        try {
            throw new \Exception('This should not send email');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();
    }

    /** @test */
    public function no_email_sent_in_local_environment_when_silent_mode_enabled(): void
    {
        // Arrange
        Config::set('app.env', 'local');
        Config::set('exception_notifier.silent_local', true);

        // Act
        try {
            throw new \Exception('Silent mode test');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();
    }

    /**
     * Create a test HTTP request
     */
    private function createTestRequest(): \Illuminate\Http\Request
    {
        return \Illuminate\Http\Request::create(
            '/test-exception',
            'GET',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '192.168.1.100',
                'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
            ]
        );
    }
}
