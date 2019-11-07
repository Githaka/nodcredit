<?php

namespace App\Mail;

use App\User;
use App\LoanApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanMoneyTransfer extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loan;


    public function __construct(User $user, LoanApplication $loan)
    {
        $this->user = $user;
        $this->loan = $loan;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.admin.fund-transfer')
            ->subject('Account credited NGN(' . number_format($this->loan->amount_approved,2) . ')');
    }
}
