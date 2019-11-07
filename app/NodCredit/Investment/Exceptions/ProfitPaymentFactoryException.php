<?php
namespace App\NodCredit\Investment\Exceptions;

use Illuminate\Support\MessageBag;

class ProfitPaymentFactoryException extends \Exception
{
    /** @var  MessageBag */
    protected $errors;

    /**
     * @param MessageBag $errors
     * @return ProfitPaymentFactoryException
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