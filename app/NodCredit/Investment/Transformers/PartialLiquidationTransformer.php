<?php

namespace App\NodCredit\Investment\Transformers;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\PartialLiquidation;

class PartialLiquidationTransformer
{

    public static function transform(PartialLiquidation $partialLiquidation, array $scopes = []): array
    {
        $array = [
            'id' => $partialLiquidation->getId(),
            'status' => $partialLiquidation->getStatus(),
            'amount' => Money::formatInNairaAsArray($partialLiquidation->getAmount()),
            'profit' => Money::formatInNairaAsArray($partialLiquidation->getProfit()),
            'penalty_amount' => Money::formatInNairaAsArray($partialLiquidation->getPenaltyAmount()),
            'penalty_percent' => $partialLiquidation->getPenaltyPercent(),
            'created_at' => $partialLiquidation->getCreatedAt()->format('Y-m-d H:i'),
            'paid_out_at' => $partialLiquidation->getPaidOutAt() ? $partialLiquidation->getPaidOutAt()->format('Y-m-d H:i') : null,
            'reason' => $partialLiquidation->getReason(),
            'is_paid' => $partialLiquidation->isPaid()
        ];

        return $array;
    }

}