<?php

namespace App\NodCredit\Account\Transformers;

use App\User;

class UserTransformer
{

    public static function transform(User $user, array $scopes = []): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'bvn' => $user->bvn,
            'bank_id' => $user->getOriginal('bank'),
            'account_name' => $user->name,
            'account_number' => $user->account_number,
            'gender' => $user->gender,
            'dob' => $user->dob,
            'scores' => (float) $user->scores
        ];
    }

}