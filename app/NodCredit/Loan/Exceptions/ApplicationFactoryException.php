<?php
namespace App\NodCredit\Loan\Exceptions;

use Illuminate\Contracts\Support\MessageBag;

class ApplicationFactoryException extends \Exception
{

    /** @var  MessageBag */
    protected $errors;

    /**
     * @param MessageBag $errors
     * @return ApplicationFactoryException
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