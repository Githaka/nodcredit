<?php

namespace App\NodCredit\Statement;

use Carbon\Carbon;

class Transaction
{
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    private $amount;
    /**
     * @var string
     */
    private $type;
    private $balance;
    /**
     * @var Carbon
     */
    private $transactionDate;
    /**
     * @var Carbon
     */
    private $valueDate;
    /**
     * @var string
     */
    private $reference;
    /**
     * @var string
     */
    private $description;


    public function __construct(string $type, $amount, $balance, Carbon $transactionDate, Carbon $valueDate, string $reference = '', string $description = '')
    {
        if (! in_array($type, [static::TYPE_DEBIT, static::TYPE_CREDIT])) {
            throw new \Exception('Transaction type error');
        }

        $this->amount = $amount;
        $this->type = $type;
        $this->balance = $balance;
        $this->transactionDate = $transactionDate;
        $this->valueDate = $valueDate;
        $this->reference = $reference;
        $this->description = $description;
    }

    public function getTransactionDate(): Carbon
    {
        return $this->transactionDate;
    }

    public function getValueDate(): Carbon
    {
        return $this->valueDate;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getAmount(): float
    {
        return floatval($this->amount);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function isCreditType(): bool
    {
        return $this->getType() === static::TYPE_CREDIT;
    }

    public function isDebitType(): bool
    {
        return $this->getType() === static::TYPE_DEBIT;
    }

}