<?php

namespace App\NodCredit\Message\TemplateHandlers;

interface HandlerInterface
{

    public static function handle(string $message, ...$params): string;

}