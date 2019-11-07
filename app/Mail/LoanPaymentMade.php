<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\LoanPayment;

class LoanPaymentMade extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $payment;

    public function __construct(LoanPayment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.users.loan-payment')
            ->subject('Loan Payment Received NGN(' . number_format($this->payment->amount,2) . ')');
    }
}
