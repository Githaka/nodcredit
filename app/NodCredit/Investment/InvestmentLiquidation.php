<?php

namespace App\NodCredit\Investment;

use App\NodCredit\Account\User;
use App\NodCredit\Investment\Exceptions\InvestmentLiquidationException;
use App\NodCredit\Investment\Models\PartialLiquidationModel;
use App\NodCredit\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;

class InvestmentLiquidation
{
    /**
     * @var Investment
     */
    private $investment;

    /**
     * @var Settings
     */
    private $settings;


    /**
     * @param Investment $investment
     * @param float $amount
     * @param string $reason
     * @param User $by
     * @return bool
     * @throws InvestmentLiquidationException
     */
    public static function liquidate(Investment $investment, float $amount, $reason = '', User $by): bool
    {
        $liquidation = new static($investment);

        $errors = $liquidation->validate($amount, $reason);

        if ($errors->any()) {
            $exception = new InvestmentLiquidationException('Data is invalid');
            $exception->setErrors($errors);

            throw $exception;
        }

        if ($amount < $liquidation->investment->getAmount()) {
            $liquidation->partialLiquidation($amount, $reason, $by);
        }
        else {
            $liquidation->fullLiquidation($reason, $by);
        }

        return true;
    }

    /**
     * InvestmentLiquidation constructor.
     * @param Investment $investment
     */
    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
        $this->settings = app(Settings::class);
    }

    public function partialLiquidation(float $amount = 0, string $reason = '', User $by): PartialLiquidation
    {

        $partialLiquidation = DB::transaction(function () use ($amount, $reason, $by) {

            /** @var ProfitPayment $payment */

            $currentProfit = $this->investment->getCalculation()->calculateProfitPerDays($this->investment->getActiveDaysCount(), $amount);
            $partialPenalty = $currentProfit * $this->getPenaltyPercentage() / 100;
            $partialProfit = $currentProfit - $partialPenalty;

            $model = PartialLiquidationModel::create([
                'investment_id' => $this->investment->getId(),
                'amount' => $amount,
                'profit' => $partialProfit,
                'penalty_amount' => $partialPenalty,
                'penalty_percent' => $this->getPenaltyPercentage(),
                'reason' => $reason,
                'liquidated_on_day' => $this->investment->getActiveDaysCount(),
                'status' => PartialLiquidation::STATUS_NEW,
                'created_by' => $by->getId(),
            ]);

            $partialLiquidation = new PartialLiquidation($model);

            $deductAmount = $amount + $partialPenalty;

            $this->investment->deductAmount($deductAmount);

            // IF NO PAYMENTS
            if (! $this->investment->getProfitPayments()->count()) {
                return $partialLiquidation;
            }

            // UPDATE PROFIT PAYMENTS
            // Single payout
            // Update scheduled profit payment
            if ($this->investment->isProfitPayoutTypeSingle()) {
                $currentPayment = $this->investment->getScheduledProfitPayments()->first();
                $currentPayment->increaseLiquidationsProfit($partialLiquidation->getProfit());
                $currentPayment->updatePrincipalProfit($this->investment->getCalculation()->calculateProfitPerDays($this->investment->getPlanDays()));
            }

            // Monthly payouts
            else if ($this->investment->isProfitPayoutTypeMonthly()) {

                // Update payment amount in current period
                $currentPayment = $this->investment->findProfitPaymentByPeriodDate(now());
                $paymentLiquidationProfit = $partialLiquidation->getProfitPerDay() * $currentPayment->getPeriodStart()->diffInDays(now());
                $currentPayment->increaseLiquidationsProfit($paymentLiquidationProfit);

                // Update scheduled payments using new principal
                $scheduledPayments = $this->investment->findScheduledProfitPaymentsFromDate(now());

                /** @var ProfitPayment $scheduledPayment */
                foreach ($scheduledPayments->all() as $scheduledPayment) {
                    $scheduledPayment->updatePrincipalProfit($this->investment->getCalculation()->calculateProfitPerDays($scheduledPayment->getPeriodDays()));
                }
            }

            return $partialLiquidation;
        });

        return $partialLiquidation;
    }

    public function fullLiquidation(string $reason = '', User $by): bool
    {
        $payoutAmount = $this->investment->getAmount();

        DB::transaction(function () use ($payoutAmount, $reason, $by) {

            if ($this->investment->isActive()) {

                // Single payout
                // Principal payout: principal amount
                // Update scheduled profit payment
                if ($this->investment->isProfitPayoutTypeSingle()) {

                    $principalProfit = $this->investment->getCalculation()->calculatePrincipalProfit();
                    $principalProfit = $principalProfit * (100 - $this->getPenaltyPercentage()) / 100;

                    $profit = $this->investment->getPartialLiquidations(true)->sumProfit() + $principalProfit;

                    /** @var ProfitPayment $payment */
                    $payment = $this->investment->getScheduledProfitPayments()->first();

                    $payment->fullLiquidationUpdate($profit);
                }

                // Monthly payouts
                // Principal payout: principal amount
                // Delete scheduled payments (except first). Update first scheduled payment
                else if ($this->investment->isProfitPayoutTypeMonthly()) {

                    $principalProfit = $this->investment->getCalculation()->calculatePrincipalProfit();
                    $principalPenalty = $principalProfit * $this->getPenaltyPercentage() / 100;

//                    $penalties = $principalPenalty + $this->investment->getPartialLiquidations(true)->sumPenalties();
//                    $payoutAmount = $this->investment->getAmount() - $penalties;

                    $payoutAmount = $this->investment->getAmount() - $principalPenalty;

                    $payment = $this->investment->getScheduledProfitPayments()->first();

                    $newPaymentPeriodDays = $payment->getPeriodStart()->diffInDays(now());
                    $newPaymentAmount = $this->investment->getCalculation()->calculateProfitPerDays($newPaymentPeriodDays);

                    $payment->fullLiquidationUpdate($newPaymentAmount);

                    $this->investment->deleteScheduledProfitPayments([$payment->getId()]);
                }
            }

            $this->investment->setLiquidated($this->investment->getActiveDaysCount(), $reason, $by);
            $this->investment->setPayout($payoutAmount, Investment::PAYOUT_STATUS_SCHEDULED);
            $this->investment->getUser()->calculateInvestBalance();
        });

        return $this->investment->isLiquidated();
    }

    public function validate(float $amount = 0, $reason = ''): MessageBag
    {
        $errors = new MessageBag();

        if ($this->investment->isLiquidated()) {
            $errors->add('investment', 'Investment is already liquidated');
        }

        if (! $amount) {
            $errors->add('amount.required', 'Please, select amount.');
        }
        else if ($amount > $this->investment->getAmount()) {
            $errors->add('amount.max', "Investment amount is less than liquidation amount.");
        }
        else if ($amount > ($this->investment->getAmount() + $this->investment->getPaidProfitPayments()->sumAmount())) {
            $errors->add('amount.max', "Liquidation amount should be less than [principal + paid profits");
        }

        if (! $reason) {
            $errors->add('reason.required', 'Please, type a reason.');
        }

        return $errors;
    }

    public function getPenaltyPercentage(): int
    {
        return $this->settings->get('investment_liquidation_penalty', 40);
    }

}