<?php

namespace App\Mail\Investment;

use App\NodCredit\Investment\PartialLiquidation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PartialLiquidationPaidMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var PartialLiquidation
     */
    public $partialLiquidation;

    /**
     * @var \App\NodCredit\Account\User
     */
    public $user;

    public function __construct(PartialLiquidation $partialLiquidation)
    {
        $this->partialLiquidation = $partialLiquidation;
        $this->user = $partialLiquidation->getInvestment()->getUser();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.users.investment.partial-liquidation-paid')
            ->subject('Investment Payment from NodCredit');
    }
}
