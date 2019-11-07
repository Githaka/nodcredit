<?php
namespace App\NodCredit\Helpers;


class Money
{

    public static function formatInNaira($amount)
    {
        return '₦' . number_format($amount);
    }

    public static function formatInNairaAsArray($amount): array
    {
        return [
            'formatted' => static::formatInNaira($amount),
            'value' => (float) $amount
        ];
    }
}