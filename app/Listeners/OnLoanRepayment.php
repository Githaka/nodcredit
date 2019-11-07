<?php

namespace App\Listeners;

use App\Events\LoanRepayment;
use App\TransactionLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OnLoanRepayment implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LoanRepayment  $event
     * @return void
     */
    public function handle(LoanRepayment $event)
    {

        $payment = $event->id;
        echo 'New payment with status ' . $payment->status . PHP_EOL;



    }
}
