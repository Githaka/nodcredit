<?php

namespace App\NodCredit\Investment;

class InvestmentReconcile
{
    /**
     * @var Investment
     */
    private $investment;

    /**
     * InvestmentReconcile constructor.
     * @param Investment $investment
     */
    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
    }

}