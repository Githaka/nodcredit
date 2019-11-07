<?php

namespace App\Listeners;

use App\Events\OnLoanPaymentMade;
use App\Mail\LoanPaymentMade;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

use App\TransactionLog;
use App\User;

class HandleLoanPaymentMade implements ShouldQueue
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
     * @param  OnLoanPaymentMade  $event
     * @return void
     */
    public function handle(OnLoanPaymentMade $event)
    {
        try{
            $data = [
                'trans_type' => 'debit',
                'payload' => $event->remark,
                'amount' => $event->payment->amount,
                'performed_by' => null,
                'status' => 'successful',
                'model' => 'LoanPayment',
                'model_id' => $event->payment->id,
                'response_message' => 'Successful',
                'pay_for' => 'Loan repayment'
            ];

            TransactionLog::create($data);



        }catch(\Exception $e) {
            echo $e->getMessage();
        }

    }
}
