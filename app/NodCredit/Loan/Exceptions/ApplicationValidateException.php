<?php
namespace App\NodCredit\Loan\Exceptions;

class ApplicationValidateException extends \Exception
{

    protected $messages = [];

    /**
     * @return bool
     */
    public function hasMessages(): bool
    {
        return !!count($this->messages);
    }

    public function setMessages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

}