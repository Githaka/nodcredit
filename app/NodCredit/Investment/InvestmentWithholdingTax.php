<?php

namespace App\NodCredit\Investment;


use Illuminate\Support\Facades\DB;

class InvestmentWithholdingTax
{

    /**
     * @param Investment $investment
     * @param int $taxPercent
     * @return bool
     */
    public static function edit(Investment $investment, int $taxPercent): bool
    {
        return DB::transaction(function() use ($investment, $taxPercent) {

            $investment->editWithholdingTax($taxPercent);

            $payments = $investment->getScheduledProfitPayments();

            /** @var ProfitPayment $payment */
            foreach ($payments->all() as $payment) {
                $payment->editWithholdingTax($taxPercent);
            }

            return true;
        });
    }
}