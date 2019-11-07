<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class StatementPeriod implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {
        $statement = $validator->getStatement();

        if (now()->diffInDays($statement->getEndAt()) > 30) {
            throw new ApplicationValidateRuleException('Statement Period end date must be less than 31 days.');
        }

        if ($statement->getStartAt()->diffInDays($statement->getEndAt()) < 20) {
            throw new ApplicationValidateRuleException('Statement Period must cover more than 19 days.');
        }

        return true;

    }
}