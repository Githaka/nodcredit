<?php

namespace App\NodCredit\Investment\Transformers;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\ProfitPayment;

class ProfitPaymentTransformer
{

    public static function transform(ProfitPayment $payment, array $scopes = []): array
    {
        $array = [
            'id' => $payment->getId(),
            'status' => $payment->getStatus(),
            'amount' => Money::formatInNairaAsArray($payment->getAmount()),
            'payout_amount' => Money::formatInNairaAsArray($payment->getPayoutAmount()),
            'withholding_tax_amount' => Money::formatInNairaAsArray($payment->getWithholdingTaxAmount()),
            'withholding_tax_percent' => $payment->getWithholdingTaxPercent(),
            'period_start' => $payment->getPeriodStart()->format('Y-m-d'),
            'period_end' => $payment->getPeriodEnd()->format('Y-m-d'),
            'scheduled_at' => $payment->getScheduledAt()->format('Y-m-d'),
            'is_auto_payout' => $payment->getAutoPayout(),
            'is_paid' => $payment->isPaid(),
            'is_scheduled' => $payment->isScheduled(),
            'is_payable' => $payment->isPayable()
        ];

        return $array;
    }

}