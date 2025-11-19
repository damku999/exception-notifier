<?php

declare(strict_types=1);

namespace Damku999\ExceptionNotifier\Tests\Feature;

use Damku999\ExceptionNotifier\Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

/**
 * Test Case 5: Bot Detection Filtering
 *
 * Verifies that bot requests don't trigger exception notification emails.
 *
 * @author Darshan Baraiya <darshan@adaptit.co.uk>
 */
class BotDetectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable exception notifications
        Config::set('exception_notifier.enabled', true);
        Config::set('exception_notifier.silent_local', false);

        // Configure bot user agents to ignore
        Config::set('exception_notifier.ignored_bots', [
            'Googlebot',
            'bingbot',
            'Baiduspider',
            'YandexBot',
            'Slurp',  // Yahoo
            'facebookexternalhit',
            'LinkedInBot',
        ]);

        // Configure test recipients
        Config::set('exception_notifier.fallback_recipients', [
            'test@example.com',
        ]);

        // Fake mail
        Mail::fake();
    }

    /** @test */
    public function googlebot_requests_do_not_send_exception_emails(): void
    {
        // Arrange
        $request = $this->createRequestWithUserAgent(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
        );

        // Act - Trigger exception from bot request
        app()->instance('request', $request);

        try {
            throw new \Exception('Exception from Googlebot');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();
    }

    /** @test */
    public function bingbot_requests_are_filtered(): void
    {
        // Arrange
        $request = $this->createRequestWithUserAgent(
            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'
        );

        // Act
        app()->instance('request', $request);

        try {
            throw new \Exception('Exception from Bingbot');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();
    }

    /** @test */
    public function normal_user_requests_send_exception_emails(): void
    {
        // Arrange
        $request = $this->createRequestWithUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        );

        // Act
        app()->instance('request', $request);

        try {
            throw new \Exception('Exception from normal user');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class);
    }

    /** @test */
    public function case_insensitive_bot_detection(): void
    {
        // Arrange - Test with different case variations
        $botVariants = [
            'googlebot',  // lowercase
            'GoogleBot',  // mixed case
            'GOOGLEBOT',  // uppercase
            'GoOgLeBoT',  // random case
        ];

        foreach ($botVariants as $botName) {
            Mail::fake();  // Reset for each iteration

            $request = $this->createRequestWithUserAgent(
                "Mozilla/5.0 (compatible; {$botName}/2.1)"
            );

            // Act
            app()->instance('request', $request);

            try {
                throw new \Exception("Exception from {$botName}");
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }

            // Assert
            Mail::assertNothingSent();
        }
    }

    /** @test */
    public function partial_bot_name_match_is_detected(): void
    {
        // Arrange - User agent contains bot name but not exact match
        $request = $this->createRequestWithUserAgent(
            'CustomCrawler/1.0 (using Googlebot technology)'
        );

        // Act
        app()->instance('request', $request);

        try {
            throw new \Exception('Exception with partial bot name');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert - Should be filtered if bot name appears anywhere in user agent
        Mail::assertNothingSent();
    }

    /** @test */
    public function social_media_bots_are_filtered(): void
    {
        // Arrange
        $socialBots = [
            'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            'LinkedInBot/1.0 (compatible; Mozilla/5.0; Apache-HttpClient +http://www.linkedin.com)',
            'Twitterbot/1.0',
        ];

        foreach ($socialBots as $botUserAgent) {
            Mail::fake();  // Reset for each iteration

            $request = $this->createRequestWithUserAgent($botUserAgent);

            // Act
            app()->instance('request', $request);

            try {
                throw new \Exception("Exception from social bot: {$botUserAgent}");
            } catch (\Exception $e) {
                $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
                $handler->report($e);
            }

            // Assert
            Mail::assertNothingSent();
        }
    }

    /** @test */
    public function bot_detection_logs_filtered_requests(): void
    {
        // Arrange
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            unlink($logPath);  // Clear log
        }

        $request = $this->createRequestWithUserAgent('Googlebot/2.1');

        // Act
        app()->instance('request', $request);

        try {
            throw new \Exception('Bot detection log test');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert
        Mail::assertNothingSent();  // No email sent

        // Should log that bot request was filtered
        $this->assertTrue(file_exists($logPath));
        $logContents = file_get_contents($logPath);
        $this->assertStringContainsString('Bot detected', $logContents);
        $this->assertStringContainsString('Googlebot', $logContents);
    }

    /** @test */
    public function empty_user_agent_sends_notification(): void
    {
        // Arrange
        $request = Request::create('/test', 'GET');
        // Don't set user agent - should be null or empty

        // Act
        app()->instance('request', $request);

        try {
            throw new \Exception('Exception with no user agent');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert - Should send email when user agent is empty/null
        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class);
    }

    /** @test */
    public function bot_list_can_be_updated_at_runtime(): void
    {
        // Arrange - Start with only Googlebot ignored
        Config::set('exception_notifier.ignored_bots', ['Googlebot']);

        // Bingbot should send email initially
        $request = $this->createRequestWithUserAgent('bingbot/2.0');
        app()->instance('request', $request);

        try {
            throw new \Exception('First attempt');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        Mail::assertSent(\Damku999\ExceptionNotifier\Mail\ExceptionNotificationMail::class);

        // Act - Add Bingbot to ignore list at runtime
        Mail::fake();  // Reset
        Config::set('exception_notifier.ignored_bots', ['Googlebot', 'bingbot']);

        $request = $this->createRequestWithUserAgent('bingbot/2.0');
        app()->instance('request', $request);

        try {
            throw new \Exception('Second attempt');
        } catch (\Exception $e) {
            $handler = app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
            $handler->report($e);
        }

        // Assert - Now should be filtered
        Mail::assertNothingSent();
    }

    /**
     * Create HTTP request with specific user agent
     */
    private function createRequestWithUserAgent(string $userAgent): Request
    {
        return Request::create(
            '/test-exception',
            'GET',
            [],
            [],
            [],
            [
                'HTTP_USER_AGENT' => $userAgent,
                'REMOTE_ADDR' => '192.168.1.100',
            ]
        );
    }
}
