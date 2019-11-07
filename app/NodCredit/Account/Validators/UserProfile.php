<?php
namespace App\NodCredit\Account\Validators;

use App\NodCredit\Account\User;
use Illuminate\Support\MessageBag;

class UserProfile
{

    public static function checkCompletion(User $user): MessageBag
    {
        $messageBag = new MessageBag();

        if (! $user->getName()) {
            $messageBag->add('name', 'Name is required');
        }

        if (! $user->getPhone()) {
            $messageBag->add('phone', 'Phone is required');
        }

        if (! $user->getEmail()) {
            $messageBag->add('email', 'Email is required');
        }

        if (! $user->getBvn()) {
            $messageBag->add('bvn', 'BVN is required');
        }

        if (! $user->getBankId()) {
            $messageBag->add('bank', 'Bank is required');
        }

        if (! $user->getAccountNumber()) {
            $messageBag->add('bank', 'Account Number is required');
        }

        if (! $user->getModel()->works()->count()) {
            $messageBag->add('works', 'Work History/Employment Information is required');
        }

        if (! $user->hasValidCard()) {
            $messageBag->add('works', 'Debit/Credit Card. Card should be valid at least 3 months and match your banking institution');
        }

        return $messageBag;
    }

}