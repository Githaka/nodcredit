<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class MinThresholdAmount
{

    public static function validate(Validator $validator, $amount): bool
    {
        $thresholdAmount = $validator->getSettings()->get('automation_rule_min_threshold_amount', 10000);

        if ($thresholdAmount > $amount) {
            throw new ApplicationValidateRuleException("Min threshold amount {$thresholdAmount} is higher than allowed amount {$amount}");
        }

        return true;
    }
}