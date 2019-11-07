<?php

namespace App\Mail;

use App\NodCredit\Loan\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Application
     */
    public $application;

    /**
     * LoanApplicationRejectedMail constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Loan Application rejected')
            ->view('emails.users.loan-application-rejected');
    }
}
