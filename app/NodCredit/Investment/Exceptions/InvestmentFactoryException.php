<?php
namespace App\NodCredit\Investment\Exceptions;

use Illuminate\Support\MessageBag;

class InvestmentFactoryException extends \Exception
{
    /** @var  MessageBag */
    protected $errors;

    /**
     * @param MessageBag $errors
     * @return InvestmentFactoryException
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