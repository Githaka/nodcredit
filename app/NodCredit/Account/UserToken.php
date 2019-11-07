<?php

namespace App\NodCredit\Account;

use App\Token;
use App\User;

class UserToken
{

    public static function create(User $user)
    {
        $token = Token::create([
            'user_id' => $user->id,
            'expire_at' => now()->addDays(30),
            'token' => str_random(150)
        ]);

        return $token;
    }

    public static function invalidate(Token $token)
    {
        $token->expire_at = now()->subYear();
        $token->save();

        return $token;
    }

    public static function delete(Token $token)
    {
        return $token->delete();
    }


}