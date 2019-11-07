<?php

namespace App\Mail\Loan;

use App\LoanApplication;
use App\NodCredit\Loan\Application;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * @var Application
     */
    public $application;

    /**
     * @var User
     */
    public $user;

    /**
     * ConfirmNewAmountMail constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
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
            ->subject('Loan Application re-confirmation')
            ->view('emails.users.loan.handling-confirmation');
    }
}
