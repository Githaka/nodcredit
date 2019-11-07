<?php

namespace App\NodCredit\Account\Policies;

use App\NodCredit\Account\User;

class PartPaymentPolicy
{

    public static function canCreate(User $user)
    {
        return $user->isAdmin();
    }

}
