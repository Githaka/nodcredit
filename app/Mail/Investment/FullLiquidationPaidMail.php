<?php

namespace App\Mail\Investment;

use App\NodCredit\Investment\Investment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FullLiquidationPaidMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var Investment
     */
    public $investment;

    /**
     * @var \App\NodCredit\Account\User
     */
    public $user;

    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
        $this->user = $investment->getUser();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.users.investment.full-liquidation-paid')
            ->subject('Investment Payment from NodCredit');
    }
}
