<?php

namespace App\NodCredit\Message\TemplateHandlers;

class ReplaceHandler implements HandlerInterface
{

    public static function handle(string $message, ...$params): string
    {
        return str_replace(array_keys($params[0]), array_values($params[0]), $message);
    }

}