<?php

namespace App\NodCredit\Loan\Transformers;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Loan\Payment;

class PaymentTransformer
{

    public static function transform(Payment $payment, array $scopes = []): array
    {
        return [
            'id' => $payment->getId(),
            'due_at' => $payment->getDueAt(),
            'status' => $payment->getStatus(),
            'amount' => Money::formatInNairaAsArray($payment->getAmount()),
            'payment_month' => (int) $payment->getPaymentMonth(),
            'created_at' => $payment->getCreatedAt(),
            'is_penalty_paused' => $payment->isPenaltyPaused(),
            'is_default' => $payment->isDefault(),
            'penalty_paused_until' => $payment->getModel()->penalty_paused_until,
            'penalty_paused_until_formatted' => $payment->getModel()->penalty_paused_until ? $payment->getModel()->penalty_paused_until->format('Y-m-d') : null,
        ];
    }

}