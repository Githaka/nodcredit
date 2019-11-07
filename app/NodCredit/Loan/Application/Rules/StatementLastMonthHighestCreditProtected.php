<?php
namespace App\NodCredit\Loan\Application\Rules;

use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Exceptions\ApplicationValidateRuleException;
use App\NodCredit\Statement\Transaction;

class StatementLastMonthHighestCreditProtected implements RuleInterface
{

    public static function validate(Validator $validator): bool
    {
        $transactions = clone $validator->getStatement()->getTransactions();
        $transactions->removeOlderThan(30);

        $redCount = $validator->getSettings()->get('automation_rule_statement_last_month_highest_credit_protected_times', 2);
        $percent = $validator->getSettings()->get('automation_rule_statement_last_month_highest_credit_protected_percent', 20);

        try {
            $credit = $transactions->getHighestCredit();
        }
        catch (\Exception $exception) {
            throw new ApplicationValidateRuleException('Statement: there are no credit transactions.');
        }

        if (! $credit) {
            throw new ApplicationValidateRuleException('Statement: there are no credit transactions.');
        }

        $debits = $transactions->getDebits();

        if ($debits->count() === 0) {
            return true;
        }

        $count = 0;

        /** @var Transaction $debit */
        foreach ($debits->all() as $debit) {
            if ($debit->getAmount() >= $percent * $credit->getAmount() / 100) {
                $count++;
            }
        }

        if ($count < $redCount) {
            return true;
        }

        throw new ApplicationValidateRuleException('Statement: last month highest Credit Amount is not protected');
    }
}