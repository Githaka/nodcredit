<?php

namespace App\Observers;

use App\LoanPayment;
/**
 * Class LoanPaymentObserver
 *
 * @package \App\Observers
 */
class LoanPaymentObserver
{

    public function creating(LoanPayment $payment)
    {
        $payment->original_amount = $payment->amount;
    }
}
