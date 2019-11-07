<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class AppIsInstalled
{

    public static function validate(Validator $validator): bool
    {

        if (! $validator->getAccountUser()->isAppInstalled() AND $validator->getAccountUser()->isAppInstallSkipped()) {
            throw new ApplicationValidateRuleException('Customer did not install app and confirmed "no mobile device"');
        }

        return true;
    }
}