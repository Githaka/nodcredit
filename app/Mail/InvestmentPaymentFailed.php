<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvestmentPaymentFailed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    public $amount;

    public $investment;


    public function __construct($amount, $investment)
    {
        $this->amount = $amount;
        $this->investment = $investment;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.admin.investment-payment-failed')
                        ->subject('Investment Payment Failed');
    }
}
