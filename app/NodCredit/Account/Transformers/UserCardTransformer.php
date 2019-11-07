<?php

namespace App\NodCredit\Account\Transformers;

use App\UserCard;

class UserCardTransformer
{

    public static function transform(UserCard $card, array $scopes = []): array
    {
        return [
            'id' => $card->id,
            'card_number' => $card->card_number,
            'exp_year' => $card->exp_year,
            'exp_month' => $card->exp_month,
            'card_type' => $card->card_type,
            'brand' => $card->brand,
            'user_id' => $card->user_id,
        ];
    }

}