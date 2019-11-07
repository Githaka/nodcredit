<?php

namespace App\Mail;

use App\User;
use App\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvestmentStartEmail extends Mailable implements  ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $investment;

    /**
     * Create a new message instance.
     *
     * @param \App\User    $user
     * @param \App\Payment $investment
     */
    public function __construct(User $user, Payment $investment)
    {
        $this->user = $user;
        $this->investment = $investment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.users.investment-start')
            ->subject('Congratulations!! Your Investment Has Started.');
    }
}
