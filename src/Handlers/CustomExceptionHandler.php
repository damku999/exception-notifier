<?php
namespace AdaptItDarshan\ExceptionNotifier\Handlers;

use Illuminate\Foundation\Exceptions\Handler as BaseExceptionHandler;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Throwable;

class CustomExceptionHandler extends BaseExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param  Throwable  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        // Call your function to handle the exception
        $this->sendExceptionEmail($e);

        // Call the parent method to ensure default logging still occurs
        parent::report($e);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  Throwable  $exception
     * @return bool
     */
    public function shouldReport(Throwable $exception)
    {
        // Define logic to determine if the exception should be reported

        $capture = config('exception-notifier.capture', []);

        if (! is_array($capture)) {
            return false;
        }

        if (in_array('*', $capture)) {
            return true;
        }

        foreach ($capture as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }

        return false;
    }


    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Throwable  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        // Render the exception into an HTTP response
        return parent::render($request, $e);
    }

    /**
     * Render an exception for the console.
     *
     * @param  \Illuminate\Console\Command  $output
     * @param  Throwable  $e
     * @return void
     */
    public function renderForConsole($output, Throwable $e)
    {
        // Render the exception for the console
        return parent::renderForConsole($output, $e);
    }

    /**
     * Send an email with the exception details.
     *
     * @param Throwable $exception
     * @return void
     */
    protected function sendExceptionEmail(Throwable $exception)
    {
        if ($this->isExceptionFromBot()) {
            return;
        }
        if ($this->isIgnoredException($exception)) {
            return;
        }
        // Generate the subject line
        $subjectLine = $this->convertExceptionToString($exception);
        Mail::to(config('exception-notifier.email'))->send(
            new \AdaptItDarshan\ExceptionNotifier\Mail\ExceptionOccurred($exception, $subjectLine)
        );
    }
    /**
     * Create a string for the given exception.
     *
     * @param  Throwable $exception
     * @return string
     */
    protected function convertExceptionToString(Throwable $exception)
    {
        // Check if the view exists and render it
        if (View::exists('exception-notifier::emails.subject')) {
            return View::make('exception-notifier::emails.subject', compact('exception'))->render();
        }

        // Fallback to default subject line if view does not exist
        return config('exception-notifier.subject', 'Exception Occurred');
    }

    /**
     * Determine if the exception is from the bot.
     *
     * @return boolean
     */
    private function isExceptionFromBot()
    {
        // Get the list of bots that we should ignore
        $ignored_bots = config('exception-notifier.ignored_bots', []);

        // Get the user agent from the request
        $agent = array_key_exists('HTTP_USER_AGENT', $_SERVER)
        ? strtolower($_SERVER['HTTP_USER_AGENT'])
        : null;

        // If the user agent is null, then we can't determine if it's a bot or not,
        // so return false
        if (is_null($agent)) {
            return false;
        }

        // Loop over the list of ignored bots
        foreach ($ignored_bots as $bot) {
            // If the user agent contains the bot's string, then it's a bot
            if ((strpos($agent, $bot) !== false)) {
                return true;
            }
        }

        // If we've reached this point, then it's not a bot
        return false;
    }

    /**
     * Determine if the exception is from the into ignore.
     *
     * @return boolean
     */
    private function isIgnoredException($exception)
    {
        $ignored_exception = config('exception-notifier.ignored_exception', []);
        if (!is_array($ignored_exception)) {
            return false;
        }

        foreach ($ignored_exception as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
