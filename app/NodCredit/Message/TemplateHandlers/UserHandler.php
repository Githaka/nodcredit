<?php

namespace App\NodCredit\Message\TemplateHandlers;

use App\User;

class UserHandler implements HandlerInterface
{

    public static function handle(string $message, ...$params): string
    {
        $user = $params[0];

        $fields = [
            '#USER_NAME#' => $user->name,
            '#USER_PHONE#' => $user->phone,
            '#USER_EMAIL#' => $user->email,
            '#USER_BANK_ACCOUNT_NUMBER#' => $user->account_number,
            '#USER_BANK_ACCOUNT_NAME#' => $user->name,
        ];

        if (strstr($message, '#USER_BANK_NAME#')) {
            $fields['#USER_BANK_NAME#'] = $user->bank ? $user->bank->name : '';
        }

        if (strstr($message, '#USER_SCORES#')) {
            $fields['#USER_SCORES#'] = $user->getScores();
        }

        if (strstr($message, '#USER_INTEREST_RATE#')) {
            $fields['#USER_INTEREST_RATE#'] = $user->getInterestRate();
        }

        return str_replace(array_keys($fields), array_values($fields), $message);
    }

}