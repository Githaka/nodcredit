<?php

namespace App\NodCredit\Message\TemplateHandlers;

use App\User;

abstract class AbstractHandler implements HandlerInterface
{

    /**
     * @var
     */
    protected $message;

    /**
     * @var User
     */
    protected $user;

    /**
     * AbstractHandler constructor.
     * @param $message
     * @param User $user
     */
    public function __construct($message, User $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    abstract public function handle(): string;

}