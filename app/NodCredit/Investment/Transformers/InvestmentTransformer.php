<?php

namespace App\NodCredit\Investment\Transformers;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;

class InvestmentTransformer
{

    public static function transform(Investment $investment, array $scopes = []): array
    {
        $array = [
            'id' => $investment->getId(),
            'status' => $investment->getStatus(),
            'amount' => Money::formatInNairaAsArray($investment->getAmount()),
            //'current_profit' => Money::formatInNairaAsArray($investment->getCalculation()->calculateCurrentProfit()),
            'profit' => Money::formatInNairaAsArray($investment->getProfit()),
            'plan_name' => $investment->getPlanName(),
            'plan_days' => $investment->getPlanDays(),
            'plan_percentage' => $investment->getPlanPercentage(),
            'started_at' => $investment->getStartedAt() ? $investment->getStartedAt()->format('Y-m-d H:i') : null,
            'started_at_days_ago' => $investment->getStartedAt() ? $investment->getStartedAt()->diffInDays(now()) : 0,
            'ended_at' => $investment->getEndedAt() ? $investment->getEndedAt()->format('Y-m-d H:i') : null,
            'maturity_date' => $investment->getMaturityDate() ? $investment->getMaturityDate()->format('Y-m-d H:i') : null,
            'liquidated_at' => $investment->getLiquidatedAt() ? $investment->getLiquidatedAt()->format('Y-m-d H:i') : null,
            'created_at' => $investment->getCreatedAt()->format('Y-m-d H:i'),
            'user_id' => $investment->getUserId(),
            'profit_payout_type' => $investment->getProfitPayoutType(),
            'is_profit_payout_type_single' => $investment->isProfitPayoutTypeSingle(),
            'is_profit_payout_type_monthly' => $investment->isProfitPayoutTypeMonthly(),
            'is_changeable' => $investment->isChangeable(),
            'is_started' => $investment->isStarted(),
            'is_ended' => $investment->isEnded(),
            'is_liquidated' => $investment->isLiquidated(),
            'is_paid_out' => $investment->isPaidOut(),
            'withholding_tax_percent' => $investment->getWithholdingTaxPercent()
        ];

        if (in_array('user', $scopes)) {
            $array['user'] = $investment->getUser()->getModel();
        }

        if (in_array('profit_payments', $scopes)) {
            $array['profit_payments'] = $investment->getProfitPayments()->transform();
        }

        if (in_array('partial_liquidations', $scopes)) {
            $array['partial_liquidations'] = $investment->getPartialLiquidations(true)->transform();
        }

        if (in_array('all_logs', $scopes)) {
            $array['all_logs'] = $investment->getAllLogs();
        }

        return $array;
    }

}