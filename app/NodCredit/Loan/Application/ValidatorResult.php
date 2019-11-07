<?php
namespace App\NodCredit\Loan\Application;

class ValidatorResult
{

    private $minAmount = 10000;
    private $maxAmount = 50000;
    private $amount;
    private $errors;
    private $messages;
    private $reject;
    private $review;
    private $deductions;

    public function __construct(float $amount, array $errors = [], array $messages = [])
    {
        $this->amount = $amount;
        $this->errors = $errors;
        $this->messages = $messages;
    }

    public function setRejectStatus(bool $value)
    {
        $this->reject = $value;
    }

    public function setReviewStatus(bool $value)
    {
        $this->review = $value;
    }

    public function setDeductions(array $deductions = [])
    {
        $this->deductions = $deductions;
    }

    public function isValid(): bool
    {
        if ($this->shouldReject() OR $this->shouldReview()) {
            return false;
        }

        return true;
    }

    public function shouldReject(): bool
    {
        return !!$this->reject;
    }

    public function shouldReview(): bool
    {
        return !!$this->review;
    }

    public function isValidStatementPeriod(): bool
    {
        return !!$this->getStatementPeriodError();
    }

    public function isStatementRecognized(): bool
    {
        return !!$this->getStatementError();
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasMessages(): bool
    {
        return !!count($this->messages);
    }

    public function hasErrors(): bool
    {
        return !!count($this->errors);
    }

    /**
     * @return null|string
     */
    public function getStatementPeriodError()
    {
        return array_get($this->errors, 'statement_period');
    }

    /**
     * @return null|string
     */
    public function getStatementError()
    {
        return array_get($this->errors, 'statement');
    }

    public function getAmountAllowed(): float
    {
        $amount = $this->amount;

        if ($amount < $this->minAmount) {
            $amount = $this->minAmount;
        }
        else if ($amount > $this->maxAmount) {
            $amount = $this->maxAmount;
        }
        else {
            $amount = $this->roundAmount($amount);
        }

        return floatval($amount);
    }

    private function roundAmount($amount): int
    {
        return 1000 * round($amount / 1000);
    }



}