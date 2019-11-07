<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class StatementInflateCredits implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {
        $credits = $validator->getStatement()->getTransactions()->getCredits();

        $credits = $credits->sortByAmount(true);

        if (! $credits->count()) {
            return false;
        }

        if ($credits->count() < 2) {
            throw new ApplicationValidateRuleException('Statement: there are no enough credit transactions for comparing.');
        }

        $first = $credits->get(0);

        $second = $credits->get(1);

        $comparePercent = $validator->getSettings()->get('automation_rule_inflate_credits_compare_percent', 20);

        if ( ($first->getAmount() * $comparePercent / 100) >= $second->getAmount()) {
            throw new ApplicationValidateRuleException('Statement: possible inflate credits.');
        }

        return true;
    }
}