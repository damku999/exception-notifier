<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Error email recipients
    |--------------------------------------------------------------------------
    |
    | Email stack traces to these addresses.
    |
     */

    'email' => env('EXCEPTION_NOTIFIER_EMAIL', ['darshan@adaptit.co.uk']),
    /*
     * The subject line of the exception email.
     *
     * This will be replaced with the result of calling
     * `convertExceptionToString` on the exception if the view
     * 'exception-notifier::emails.subject' does not exist.
     *
     * @var string
     */
    // 'subject' => 'Exception Occurred',

    /*
    |--------------------------------------------------------------------------
    | A list of the exception types that should be captured.
    |--------------------------------------------------------------------------
    |
    | For which exception class emails should be sent?
    |
    | You can also use '*' in the array which will in turn captures every
    | exception.
    |
     */
    'capture' => [
        // \Symfony\Component\ErrorHandler\Error\FatalError::class,
        '*',
    ],

    /*
     * List of exception classes that should not trigger an email to be sent.
     *
     * This is useful when you want to ignore certain exceptions that are
     * not important enough to be emailed about.
     *
     * @var array
     */
    'ignored_exception' => [
        // \Illuminate\Validation\ValidationException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignore Crawler Bots
    |--------------------------------------------------------------------------
    |
    | For which bots should we NOT send error emails?``
    |
     */
    'ignored_bots' => [
        'yandexbot', // YandexBot
        'googlebot', // Googlebot
        'bingbot', // Microsoft Bingbot
        'slurp', // Yahoo! Slurp
        'ia_archiver', // Alexa
    ],
];
