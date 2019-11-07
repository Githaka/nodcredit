<?php

namespace App\NodCredit\Investment;


use App\NodCredit\Investment\Collections\ProfitPaymentCollection;
use App\NodCredit\Investment\Factories\ProfitPaymentFactory;
use Carbon\Carbon;

class InvestmentProfitPaymentBuilder
{

    /**
     * @var Investment
     */
    private $investment;

    public static function build(Investment $investment, bool $deleteProfitPayments = false)
    {
        if (! $investment->isStarted()) {
            throw new \Exception('Investment has not started yet.');
        }

        $currentPayments = $investment->getProfitPayments();

        // Exists
        if ($currentPayments->count()) {

            if ($investment->hasPaidProfitPayments()) {
                throw new \Exception('You can`t re-build a profit payments. Investment has a paid profit payments.');
            }

            if (! $deleteProfitPayments) {
                throw new \Exception('Investment already has a profit payments. Please, delete a current payments first.');
            }

            // Delete
            $investment->deleteProfitPayments();
        }

        $builder = new static($investment);

        if ($investment->isProfitPayoutTypeSingle()) {
            return $builder->buildSinglePayment();
        }

        if ($investment->isProfitPayoutTypeMonthly()) {
            return $builder->buildMonthlyPayments();
        }

        throw new \Exception("Profit payout type [{$investment->getProfitPayoutType()}] is not supported.");
    }

    public function __construct(Investment $investment)
    {
        $this->investment = $investment;
    }

    private function buildSinglePayment()
    {
        $data = [
            'investment_id' => $this->investment->getId(),
            'amount' => $this->investment->getCalculation()->calculateProfitPerDays($this->investment->getPlanDays()),
            'status' => ProfitPayment::STATUS_SCHEDULED,
            'period_days' => $this->investment->getPlanDays(),
            'period_start' => $this->investment->getStartedAt()->format('Y-m-d H:i:s'),
            'period_end' => $this->investment->getMaturityDate()->format('Y-m-d H:i:s'),
            'scheduled_at' => $this->investment->getMaturityDate()->copy()->addDay()->format('Y-m-d H:i:s'),
            'auto_payout' => true,
        ];

        try {
            $payment = ProfitPaymentFactory::create($data);
        }
        catch (\Exception $exception) {
            throw $exception;
        }

        return $payment;
    }

    private function buildMonthlyPayments()
    {
        $startedAt = new \DateTime($this->investment->getStartedAt()->toDateTimeString());
        $endedAt = new \DateTime($this->investment->getMaturityDate()->toDateTimeString());
        $interval = new \DateInterval('P30D');
        $periods = new \DatePeriod($startedAt, $interval ,$endedAt);

        /** @var \DateTime $periodStart */
        foreach ($periods as $periodStart) {

            $periodEnd = clone $periodStart;
            $periodEnd->add($interval);

            if ($periodEnd->diff($endedAt)->invert) {
                $periodEnd = $endedAt;
            }

            $periodDays = $periodStart->diff($periodEnd)->days;

            $scheduledAt = clone $periodEnd;
            $scheduledAt->modify('+1 day');

            $data = [
                'investment_id' => $this->investment->getId(),
                'amount' => $this->investment->getCalculation()->calculateProfitPerDays($periodDays),
                'status' => ProfitPayment::STATUS_SCHEDULED,
                'period_days' => $periodDays,
                'period_start' => $periodStart->format('Y-m-d H:i:s'),
                'period_end' => $periodEnd->format('Y-m-d H:i:s'),
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
                'auto_payout' => true,
            ];

            try {
                ProfitPaymentFactory::create($data);
            }
            catch (\Exception $exception) {
                throw $exception;
            }
        }
    }



}