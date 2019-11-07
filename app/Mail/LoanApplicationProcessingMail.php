<?php

namespace App\Mail;

use App\LoanApplication;
use App\NodCredit\Loan\Application;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanApplicationProcessingMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var LoanApplication
     */
    public $loan;

    /**
     * @var User
     */
    public $user;

    /**
     * LoanApplicationProcessingMail constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->loan = $application->getModel();
        $this->user = $application->getUser();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Your Loan is being processed')
            ->view('emails.users.loan-processing');
    }
}
