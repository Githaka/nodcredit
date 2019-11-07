<?php
namespace App\NodCredit\Loan\Exceptions;

class ApplicationPauseException extends \Exception
{
    protected $message = 'Application pause exception';
}