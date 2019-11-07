<?php

namespace App\NodCredit\Loan;

class DecrementalConfig
{
    /** @var int */
    private $minAmount;

    /** @var int */
    private $decreasePercent;

    /** @var int */
    private $level;
    /**
     * @var int
     */
    private $startPercent;

    /**
     * DecrementalConfig constructor.
     * @param int $decreasePercent
     * @param int $minAmount
     * @param int $level
     * @param int $startPercent
     */
    public function __construct($decreasePercent = 50, $minAmount = 100, $level = 0, int $startPercent = 100)
    {
        $this->decreasePercent = $decreasePercent;
        $this->minAmount = $minAmount;
        $this->level = $level;
        $this->startPercent = $startPercent;
    }

    public function getMinAmount(): int
    {
        return (int) $this->minAmount;
    }

    public function getDecreasePercent(): int
    {
        return (int) $this->decreasePercent;
    }

    public function getLevel(): int
    {
        return (int) $this->level;
    }

    public function getStartPercent(): int
    {
        return (int) $this->startPercent;
    }
}