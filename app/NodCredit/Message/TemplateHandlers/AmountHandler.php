<?php

namespace App\NodCredit\Message\TemplateHandlers;

class AmountHandler implements HandlerInterface
{

    public static function handle(string $message, ...$params): string
    {
        return str_replace($params[0], 'NGN ' . number_format($params[1], 2), $message);
    }

}