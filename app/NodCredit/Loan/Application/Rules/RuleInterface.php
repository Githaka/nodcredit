<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;

interface RuleInterface
{

    public static function validate(Validator $validator): bool;

}