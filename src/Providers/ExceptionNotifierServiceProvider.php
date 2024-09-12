<?php
namespace AdaptItDarshan\ExceptionNotifier\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use AdaptItDarshan\ExceptionNotifier\Handlers\CustomExceptionHandler;

class ExceptionNotifierServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the custom exception handler
        $this->app->singleton(ExceptionHandler::class, function ($app) {
            return new CustomExceptionHandler($app);
        });
    }

    public function boot()
    {
        // Publish configuration if needed
        $this->publishes([
            __DIR__.'/../config/exception-notifier.php' => config_path('exception-notifier.php'),
        ], 'config');

        // Publish views
        $this->publishes([
            __DIR__.'/../Views' => resource_path('views/vendor/exception-notifier'),
        ], 'views');

        // Register the view namespace
        $this->loadViewsFrom(__DIR__.'/../Views', 'exception-notifier');
    }
}
