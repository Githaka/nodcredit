<?php

namespace App\NodCredit\Investment;

class InvestmentCalculation
{
    /**
     * @var Investment
     */
    private $investment;

    /**
     * InvestmentCalculation constructor.
     * @param Investment $investment
     */
    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
    }

    public function calculateProfitPerDay(float $amount = null): float
    {
        $amount = $amount ?: $this->investment->getAmount();

        $perYear = $amount * $this->investment->getPlanPercentage() / 100;

        $perDay = number_format($perYear / 365, 2, '.' ,'');

        return floatval($perDay);
    }

    public function calculateProfitPerDays(int $days, float $amount = null): float
    {
        $amount = $amount ?: $this->investment->getAmount();

        $perDays = number_format($this->calculateProfitPerDay($amount) * $days, 2, '.' ,'');

        return floatval($perDays);
    }

    public function calculatePrincipalProfit(): float
    {
        $perDay = $this->calculateProfitPerDay($this->investment->getAmount());

        $profit = number_format($this->investment->getActiveDaysCount() * $perDay, 2, '.' ,'');

        return floatval($profit);
    }

    public function calculatePayoutAmount(): float
    {
        return $this->investment->getAmount();
    }

    public function calculateWithholdingTaxAmount(float $amount): float
    {
        $tax = number_format($amount * $this->investment->getWithholdingTaxPercent() / 100, 2, '.', '');

        return floatval($tax);
    }

}