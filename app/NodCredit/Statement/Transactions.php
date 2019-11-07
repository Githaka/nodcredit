<?php

namespace App\NodCredit\Statement;

use Illuminate\Support\Collection;

class Transactions
{
    /**
     * @var Transaction[]
     * @var \Illuminate\Support\Collection
     */
    private $items;

    private $headers = [
        'Transaction Date',
        'Value Date',
        'Reference',
        'Debit',
        'Credit',
        'Balance',
        'Description'
    ];

    public function __construct(Collection $collection = null)
    {
        $this->items = $collection ?: collect();
    }

    /**
     * @param int $key
     * @return Transaction|null
     */
    public function get(int $key = 0)
    {
        return $this->items->get($key);
    }

    public function removeOlderThan(int $days): self
    {
        $startDate = now()->subDays($days);
        $startDate->startOfDay();

        $this->items = $this->items->filter(function(Transaction $item) use($startDate) {
            return $item->getTransactionDate()->greaterThanOrEqualTo($startDate);
        });

        $this->items = $this->items->values();

        return $this;
    }

    public function removeByKeywords(array $keywords = [])
    {
        $pattern = implode('|', $keywords);

        $this->items = $this->items->filter(function(Transaction $item) use($pattern) {

            if (preg_match('#' . $pattern . '#iu', $item->getDescription())) {
                return false;
            }

            return true;
        });

        $this->items = $this->items->values();

        return $this;
    }

    public function removeDuplicates(): self
    {
        $this->items = $this->items->unique(function(Transaction $item) {
            $date = clone $item->getTransactionDate();
            $date->startOfDay();

            return md5("{$date}_{$item->getType()}_{$item->getAmount()}_{$item->getBalance()}");
        });

        $this->items = $this->items->values();

        return $this;
    }


    /**
     * @return Transaction|null
     */
    public function getLastTransaction()
    {
        $sorted = $this->items->sortByDesc(function(Transaction $transaction) {
            return $transaction->getTransactionDate()->timestamp;
        });

        return $sorted->first();
    }

    /**
     * @return Transaction|null
     */
    public function getFirstTransaction()
    {
        $sorted = $this->items->sortBy(function(Transaction $transaction) {
            return $transaction->getTransactionDate()->timestamp;
        });

        return $sorted->first();
    }

    public function getCredits(): self
    {
        $transactions = new static();

        foreach ($this->items as $item) {
            if ($item->isCreditType()) {
                $transactions->push($item);
            }
        }

        return $transactions;
    }

    public function getDebits(): self
    {
        $transactions = new static();

        foreach ($this->items as $item) {
            if ($item->isDebitType()) {
                $transactions->push($item);
            }
        }

        return $transactions;
    }

    public function getHighestCredit()
    {
        $credits = $this->items->filter(function(Transaction $item) {
            return $item->isCreditType();
        });

        return $credits
            ->sortByDesc(function(Transaction $item){
                return $item->getAmount();
            })
            ->first();
    }

    public function getHighestDebit()
    {
        $debits = $this->items->filter(function(Transaction $item) {
            return $item->isDebitType();
        });

        return $debits
            ->sortByDesc(function(Transaction $item){
                return $item->getAmount();
            })
            ->first();
    }

    public function getCreditsAmount(): float
    {
        $amount = 0;

        $this->items->each(function(Transaction $item) use(&$amount) {
            if ($item->isCreditType()) {
                $amount += $item->getAmount();
            }
        });

        return floatval($amount);
    }

    public function getDebitsAmount(): float
    {
        $amount = 0;

        $this->items->each(function(Transaction $item) use(&$amount) {
            if ($item->isDebitType()) {
                $amount += $item->getAmount();
            }
        });

        return floatval($amount);
    }

    public function findSalaryTransactions(): self
    {
        $transactions = new static();

        /** @var Transaction $item */
        foreach ($this->items as $item) {
            if ($item->isCreditType() AND preg_match('#salary#iu', $item->getDescription())) {
                $transactions->push($item);
            }
        }

        return $transactions;
    }

    public function findTransactionsByKeywords(array $keywords = []): self
    {
        $transactions = new static();

        $pattern = '#' . implode('|', $keywords) . '#iu';

        /** @var Transaction $item */
        foreach ($this->items as $item) {
            if (preg_match($pattern, $item->getDescription())) {
                $transactions->push($item);
            }
        }

        return $transactions;
    }

    public function hasUserNameInDescription(string $name): bool
    {
        $name = str_replace([',', '.'], ' ', $name);
        $name = preg_replace('#\s{2,}#', ' ', $name);
        $name = trim(strtoupper($name));
        $nameParts = explode(' ', $name);

        /** @var Transaction $item */
        foreach ($this->items as $item) {
            $match = 0;

            foreach ($nameParts as $part) {
                if (strstr($item->getDescription(), $part) !== false) {
                    $match++;
                }
            }

            if ($match >= 2) {
                return true;
            }
        }

        return false;
    }

    public function calculateBalanceBetweenTransactions(): float
    {
        $balance = 0;

        /** @var Transaction $item */
        foreach ($this->items as $item) {
            if ($item->isCreditType()) {
                $balance += $item->getAmount();
            }
            else {
                $balance -= $item->getAmount();
            }
        }

        return (float) $balance;
    }

    public function sortByAmount(bool $descending = false): self
    {
        $sortedItems = $this->items->sortBy(
            function(Transaction $item) {
                return $item->getAmount();
            },
            SORT_REGULAR,
            $descending
        );

        $sortedItems = $sortedItems->values();

        return new static($sortedItems);
    }


    public function push(Transaction $transaction)
    {
        $this->items->push($transaction);

        return $this;
    }

    public function all(): array
    {
        return $this->items->all();
    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function toHtml(): string
    {
        $html = '<table style="border: 1px solid; border-collapse: collapse;"><thead><tr><th style="border: 1px solid; padding: 5px;">#</th><th style="border: 1px solid; padding: 5px;">' . implode('</th><th style="border: 1px solid; padding: 5px;">', $this->headers) . '</th></tr></thead>';

        /** @var Transaction $transaction */
        foreach ($this->items as $index => $transaction) {
            $html .= '<tr><td style="border: 1px solid; padding: 5px;">' . ($index + 1) . ' </td>';

            $html .= '<td style="border: 1px solid; padding: 5px;"> ' . $transaction->getTransactionDate() . '</td>';
            $html .= '<td style="border: 1px solid; padding: 5px;"> ' . $transaction->getValueDate() . '</td>';
            $html .= '<td style="border: 1px solid; padding: 5px;"> ' . $transaction->getReference() . '</td>';

            if ($transaction->isDebitType()) {
                $html .= '<td style="border: 1px solid; padding: 5px;"> ' . $transaction->getAmount() . '</td><td style="border: 1px solid; padding: 5px;"></td>';
            }
            else if ($transaction->isCreditType()) {
                $html .= '<td style="border: 1px solid; padding: 5px;"></td><td style="border: 1px solid; padding: 5px;"> ' . $transaction->getAmount() . '</td>';
            }

            $html .= '<td style="border: 1px solid; padding: 5px;"> ' . $transaction->getBalance() . '</td>';
            $html .= '<td style="border: 1px solid; padding: 5px;"> ' . $transaction->getDescription() . '</td>';

            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

}