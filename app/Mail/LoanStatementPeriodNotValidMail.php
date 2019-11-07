<?php

namespace App\Mail;

use App\NodCredit\Loan\Application;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanStatementPeriodNotValidMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Application
     */
    public $application;

    /**
     * @var string
     */
    public $reason;

    /**
     * LoanStatementPeriodNotValidMail constructor.
     * @param Application $application
     * @param string $reason
     */
    public function __construct(Application $application, string $reason = '')
    {
        $this->application = $application;
        $this->user = $application->getUser();
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Loan Application: Bank Statement period is not valid')
            ->view('emails.users.loan-statement-period-not-valid');
    }
}
