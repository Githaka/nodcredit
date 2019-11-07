<?php

namespace App\NodCredit\Account\Policies;

use App\NodCredit\Account\User;

class LoanPaymentPolicy
{

    public static function canIncreaseAmount(User $user)
    {
        return $user->isAdmin();
    }

    public static function canMarkAsPaid(User $user)
    {
        return $user->isAdmin();
    }

    public static function canAddPartPayment(User $user)
    {
        return $user->isAdmin();
    }

}
