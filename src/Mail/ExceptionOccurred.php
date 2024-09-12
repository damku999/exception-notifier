<?php

namespace AdaptItDarshan\ExceptionNotifier\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExceptionOccurred extends Mailable
{
    use Queueable, SerializesModels;

    public $exception;
    protected $subjectLine;

/**
 * Create a new message instance.
 *
 * @param  Throwable  $exception
 * @param  string|null  $subjectLine
 * @return void
 */
    public function __construct(Throwable $exception, $subjectLine = null)
    {
        $this->exception = $exception;
        $this->subjectLine = $subjectLine;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('exception-notifier::emails.exception_occurred')
            ->with(['exception' => $this->exception])
            ->subject($this->subjectLine ?? 'Exception Occurred'); // Use the provided subject line or a default
    }
}
