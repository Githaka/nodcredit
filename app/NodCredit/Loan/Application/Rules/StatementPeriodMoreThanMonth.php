<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class StatementPeriodMoreThanMonth implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {

        if ($validator->getStatement()->getPeriodDaysValidCount() > 30) {
            return true;
        }

        throw new ApplicationValidateRuleException('Statement period is less than one month');
    }

}