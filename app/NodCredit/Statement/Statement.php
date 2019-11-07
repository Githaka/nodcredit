<?php

namespace App\NodCredit\Statement;

use Carbon\Carbon;

class Statement
{
    private $file;
    private $bankName;
    private $accountNumber;
    private $customerName;
    private $startAt;
    private $endAt;
    private $transactions;
    private $closingBalance;
    private $transactionValidAge = 90;

    public function __construct(string $file = '')
    {
        $this->file = $file;
        $this->transactions = new Transactions();
    }

    public function setAccountNumber(string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function setBankName(string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBankName(): string
    {
        return $this->bankName;
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function setCustomerName(string $name): self
    {
        $this->customerName = $name;

        return $this;
    }

    public function getCustomerName()
    {
        return $this->customerName;
    }

    public function setPeriod(Carbon $startAt, Carbon $endAt): self
    {
        $this->startAt = $startAt;
        $this->endAt = $endAt;

        return $this;
    }

    public function setStartAt(Carbon $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function setEndAt(Carbon $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getStartAt(): Carbon
    {
        return $this->startAt;
    }

    public function getEndAt(): Carbon
    {
        return $this->endAt;
    }

    public function useFirstTransactionAsStartAt(): bool
    {
        if ($firstTransaction = $this->getTransactions()->getFirstTransaction()) {
            $this->setStartAt($firstTransaction->getTransactionDate());

            return true;
        }

        return false;
    }

    public function useLastTransactionAsEndAt(): bool
    {
        if ($lastTransaction = $this->getTransactions()->getLastTransaction()) {
            $this->setEndAt($lastTransaction->getTransactionDate());

            return true;
        }

        return false;
    }

    public function setTransactions(Transactions $transactions): self
    {
        $transactions
            ->removeOlderThan($this->transactionValidAge)
            ->removeByKeywords(['nodcredit'])
            ->removeDuplicates()
        ;

        $this->transactions = $transactions;

        return $this;
    }

    public function getTransactions(): Transactions
    {
        return $this->transactions;
    }

    public function getLastTransaction()
    {
        try {
            return $this->getTransactions()->getLastTransaction();
        }
        catch (\Exception $exception) {
            return null;
        }
    }

    public function useLastTransactionAsClosingBalance(): self
    {
        if ($transaction = $this->getLastTransaction()) {
            $this->setClosingBalance($transaction->getBalance());
        }

        return $this;
    }

    public function getClosingBalance(): float
    {
        return floatval($this->closingBalance);
    }

    public function setClosingBalance($value): self
    {
        $this->closingBalance = $value;

        return $this;
    }

    public function getCreditsAmount(): float
    {
        return $this->getTransactions()->getCreditsAmount();
    }

    public function getDebitsAmount(): float
    {
        return $this->getTransactions()->getDebitsAmount();
    }

    public function getPeriodDaysValidCount(): int
    {
        $days = now()->diffInDays($this->getStartAt());

        return $days < $this->transactionValidAge ? $days : $this->transactionValidAge;
    }

    public function getMonthlyAvgCreditsAmount()
    {
        $creditsAmount = $this->getCreditsAmount();

        $days = now()->diffInDays($this->getStartAt());

        if ($days > $this->transactionValidAge) {
            $monthlyAvgAmount = $creditsAmount / 3;
        }
        else {
            $monthlyAvgAmount = $creditsAmount / $days * 30;
        }

        return $monthlyAvgAmount;
    }

    public function findSalaryTransactions(): Transactions
    {
        return $this->getTransactions()->findSalaryTransactions();
    }

    public function findLendersTransactions(array $lenders = []): Transactions
    {
        return $this->getTransactions()->findTransactionsByKeywords($lenders);
    }

    public function countLendersInTransactions(array $lenders = []): int
    {
        $transactions = $this->findLendersTransactions($lenders);

        if (! $transactions->count()) {
            return 0;
        }

        $found = [];

        /** @var Transaction $transaction */
        foreach ($transactions->all() as $transaction) {
            foreach ($lenders as $lender) {
                $lender = trim($lender);

                if (preg_match('#' . $lender . '#iu', $transaction->getDescription())) {
                    $found[md5($lender)] = array_get($found, md5($lender), 0) + 1;
                }
            }
        }

        return count($found);
    }

    public function removeDuplicates(): self
    {
        $this->getTransactions()->removeDuplicates();

        return $this;
    }
}