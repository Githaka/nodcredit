<?php
namespace App\NodCredit\Account\Exceptions;

use Illuminate\Support\MessageBag;
use Throwable;

class UserFactoryException extends \Exception
{
    /** @var  MessageBag */
    private $errors;

    public function __construct($message = "", $code = 0, Throwable $previous = null, MessageBag $errors = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors ?: app(MessageBag::class);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}