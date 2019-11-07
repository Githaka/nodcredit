<?php

namespace App\NodCredit\Investment\Factories;

use App\NodCredit\Account\User;
use App\NodCredit\Investment\Exceptions\InvestmentFactoryException;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Models\InvestmentModel;
use App\NodCredit\Settings;
use App\Payment;
use Illuminate\Support\MessageBag;

class InvestmentFactory
{

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Payment $payment
     * @return Investment
     * @throws InvestmentFactoryException
     */
    public static function createUsingPayment(Payment $payment): Investment
    {
        if (! $payment->is_investment) {
            throw new InvestmentFactoryException('Payment must have [investment] reason');
        }

        if ($payment->status !== 'success') {
            throw new InvestmentFactoryException('Payment status must be [success]');
        }

        $factory = new static();

        $errors = $factory->validate(
            $payment->user_id,
            $payment->amount,
            $payment->investment_tenor,
            Investment::PROFIT_PAYOUT_TYPE_MONTHLY
        );

        if ($errors->any()) {
            $exception = new InvestmentFactoryException('Data is invalid');
            $exception->setErrors($errors);

            throw $exception;
        }

        $selectedPlan = $factory->findPlan($payment->investment_tenor);

        $model = InvestmentModel::create([
            'user_id' => $payment->user_id,
            'status' => Investment::STATUS_NEW,
            'amount' => $payment->amount,
            'original_amount' => $payment->amount,
            'plan_value' => $selectedPlan->value,
            'plan_name' => $selectedPlan->name,
            'plan_days' => $selectedPlan->days,
            'plan_percentage' => $selectedPlan->percentage,
            'profit_payout_type' => Investment::PROFIT_PAYOUT_TYPE_MONTHLY,
            'created_by' => $payment->user_id,
            'payment_id' => $payment->id,
            'withholding_tax_percent' => $factory->getSettings()->get('investment_default_withholding_tax', 10)
        ]);

        $investment = new Investment($model);

        $user = $investment->getUser();

        $user->calculateInvestBalance();

        return $investment;
    }

    public static function create(string $userId, int $amount, string $tenor, User $by, string $profitPayoutType): Investment
    {
        $factory = new static();

        $errors = $factory->validate($userId, $amount, $tenor, $profitPayoutType);

        if ($errors->any()) {
            $exception = new InvestmentFactoryException('Data is invalid');
            $exception->setErrors($errors);

            throw $exception;
        }

        $selectedPlan = $factory->findPlan($tenor);

        $model = InvestmentModel::create([
            'user_id' => $userId,
            'status' => Investment::STATUS_NEW,
            'amount' => $amount,
            'original_amount' => $amount,
            'plan_value' => $selectedPlan->value,
            'plan_name' => $selectedPlan->name,
            'plan_days' => $selectedPlan->days,
            'plan_percentage' => $selectedPlan->percentage,
            'profit_payout_type' => $profitPayoutType,
            'created_by' => $by->getId(),
            'withholding_tax_percent' => $factory->getSettings()->get('investment_default_withholding_tax', 10)
        ]);

        $investment = new Investment($model);

        $user = $investment->getUser();

        $user->calculateInvestBalance();

        return $investment;
    }

    public function __construct()
    {
        $this->settings = app(Settings::class);
        $this->plans = json_decode($this->settings->get('investmentConfig'));
    }

    public function validate(string $userId, int $amount, string $tenor, string $profitPayoutType): MessageBag
    {
        $errors = new MessageBag();

        if (! $selectedPlan = $this->findPlan($tenor)) {
            $errors->add('plan', "Investment plan [{$tenor}] not found in the list ");
        }

        if ($amount > $this->settings->get('investment_max_amount')) {
            $errors->add('amount.max', "Investment amount {$amount} is greater than max allowed amount.");
        }

        if ($amount < $this->settings->get('investment_min_amount')) {
            $errors->add('amount.max', "Investment amount {$amount} is less than min allowed amount.");
        }

        if (! in_array($profitPayoutType, Investment::getProfitPayoutTypeValues())) {
            $errors->add('profit_payout_type', "Please, select a valid profit payout type.");
        }

        return $errors;
    }

    public function findPlan(string $value)
    {
        foreach ($this->plans as $plan) {
            if ($plan->value === $value) {
                return $plan;
            }
        }

        return null;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

}