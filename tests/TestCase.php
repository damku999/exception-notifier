<?php

declare(strict_types=1);

namespace Damku999\ExceptionNotifier\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Damku999\ExceptionNotifier\ExceptionNotifierServiceProvider;

/**
 * Base Test Case
 *
 * Provides common testing functionality for all exception notifier tests.
 *
 * @author Darshan Baraiya <darshan@adaptit.co.uk>
 */
abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup can be added here
    }

    /**
     * Get package providers
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ExceptionNotifierServiceProvider::class,
        ];
    }

    /**
     * Define environment setup
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Set test environment
        $app['config']->set('app.env', 'testing');
        $app['config']->set('app.debug', true);

        // Configure test database
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure test cache
        $app['config']->set('cache.default', 'array');

        // Configure test mail
        $app['config']->set('mail.default', 'array');

        // Default exception notifier configuration for tests
        $app['config']->set('exception_notifier', [
            'enabled' => true,
            'silent_local' => false,
            'fallback_recipients' => ['test@example.com'],
            'rate_limit_strategy' => 'per_signature',
            'max_emails_per_signature_per_hour' => 10,
            'critical_exceptions' => [],
            'ignored_exceptions' => [],
            'ignored_bots' => [],
            'branding' => [
                'name' => 'Test Application',
                'logo_url' => null,
                'primary_color' => '#3490dc',
                'support_email' => 'support@test.com',
            ],
        ]);
    }

    /**
     * Create a test exception with specific attributes
     *
     * @param string $class
     * @param string $message
     * @param int $code
     * @return \Exception
     */
    protected function createTestException(
        string $class = \RuntimeException::class,
        string $message = 'Test exception',
        int $code = 0
    ): \Exception {
        return new $class($message, $code);
    }

    /**
     * Assert that an email was sent with specific data
     *
     * @param string $mailClass
     * @param callable $callback
     * @return void
     */
    protected function assertEmailSentWith(string $mailClass, callable $callback): void
    {
        \Illuminate\Support\Facades\Mail::assertSent($mailClass, $callback);
    }

    /**
     * Get exception handler instance
     *
     * @return \Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler
     */
    protected function getExceptionHandler(): \Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler
    {
        return app(\Damku999\ExceptionNotifier\Exceptions\JsonExceptionHandler::class);
    }
}
