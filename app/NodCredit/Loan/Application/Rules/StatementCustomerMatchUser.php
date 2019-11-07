<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;

class StatementCustomerMatchUser implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {
        $statementNameString = $validator->getStatement()->getCustomerName();
        $statementName = self::prepareName($statementNameString);
        $statementName = explode(' ', $statementName);

        $userNameString = $validator->getLoanApplication()->owner->name;
        $userName = self::prepareName($userNameString);
        $userName = explode(' ', $userName);

        $diff = array_diff($statementName, $userName);

        if (count($diff) === 0 AND count($userName) === count($statementName)) {
            return true;
        }

        if (count($diff) === 1 AND (count($userName) > 2 AND count($statementName) > 1)) {
            return true;
        }

        if (count($diff) === 1 AND (count($userName) > 1 AND count($statementName) > 2)) {
            return true;
        }

        throw new ApplicationValidateRuleException("Statement customer name <b>$statementNameString</b> does not match user name <b>$userNameString</b>.");
    }

    public static function prepareName(string $name): string
    {
        $name = str_replace([',', '.'], ' ', $name);
        $name = preg_replace('#\s{2,}#', ' ', $name);
        $name = trim(strtoupper($name));

        return $name;
    }
}