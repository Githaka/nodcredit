<?php
namespace App\NodCredit\Investment\Exceptions;

use Illuminate\Contracts\Support\MessageBag;

class InvestmentLiquidationException extends \Exception
{
    /** @var  MessageBag */
    protected $errors;

    /**
     * @param MessageBag $errors
     * @return InvestmentLiquidationException
     */
    public function setErrors(MessageBag $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return MessageBag|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

}