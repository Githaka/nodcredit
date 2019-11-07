<?php

namespace App\Mail;

use App\User;
use App\LoanApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoanEmailNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    public $user;
    public $loan;

    /**
     * Create a new message instance.
     *
     * @param \App\User            $user
     * @param \App\LoanApplication $loan
     */
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
        return $this->view('emails.users.loan-status-notification')
                ->subject('Update on your loan #(' . $this->loan->status . ')');
    }
}
