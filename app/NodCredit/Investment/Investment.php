<?php

namespace App\NodCredit\Investment;

use App\NodCredit\Account\User;
use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Collections\PartialLiquidationCollection;
use App\NodCredit\Investment\Collections\ProfitPaymentCollection;
use App\NodCredit\Investment\Models\InvestmentModel;
use App\NodCredit\Investment\Models\ProfitPaymentModel;
use App\NodCredit\Investment\Transformers\InvestmentTransformer;
use App\NodCredit\Settings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Investment
{

    const STATUS_NEW = 'new';
    const STATUS_ACTIVE = 'active';
    const STATUS_LIQUIDATED = 'liquidated';
    const STATUS_ENDED = 'ended';

    const PAYOUT_STATUS_SCHEDULED = 'scheduled';
    const PAYOUT_STATUS_PAID = 'paid';
    const PAYOUT_STATUS_FAILED = 'failed';

    const PROFIT_PAYOUT_TYPE_SINGLE = 'single';
    const PROFIT_PAYOUT_TYPE_MONTHLY = 'monthly';

    /**
     * @var InvestmentModel
     */
    private $model;

    /**
     * @var User
     */
    private $user;

    /**
     * @var PartialLiquidationCollection
     */
    private $partialLiquidations;

    /** @var Settings  */
    private $settings;

    /**
     * @var InvestmentCalculation
     */
    private $calculation;

    /**
     * @param string $id
     * @return null|static
     */
    public static function find(string $id)
    {
        $model = InvestmentModel::find($id);

        if (! $model) {
            return null;
        }

        return new static($model);
    }

    public static function getProfitPayoutTypes(): array
    {
        return [
            static::PROFIT_PAYOUT_TYPE_SINGLE => ['name' => 'In the end', 'value' => static::PROFIT_PAYOUT_TYPE_SINGLE],
            static::PROFIT_PAYOUT_TYPE_MONTHLY => ['name' => 'Monthly', 'value' => static::PROFIT_PAYOUT_TYPE_MONTHLY]
        ];
    }

    public static function getProfitPayoutTypeValues(): array
    {
        return [
            static::PROFIT_PAYOUT_TYPE_SINGLE,
            static::PROFIT_PAYOUT_TYPE_MONTHLY
        ];
    }

    /**
     * Investment constructor.
     * @param InvestmentModel $model
     */
    public function __construct(InvestmentModel $model)
    {
        $this->model = $model;
        $this->calculation = new InvestmentCalculation($this);
        $this->settings = app(Settings::class);
    }


    public function getId(): string
    {
        return $this->model->id;
    }

    public function getUserId(): string
    {
        return $this->model->user_id;
    }

    public function getUser(): User
    {
        if (! $this->user) {
            $this->user = new User($this->model->user);
        }

        return $this->user;
    }

    /**
     * @param bool $format
     * @return array|float
     */
    public function getAmount(bool $format = false)
    {
        $amount = (float) $this->model->amount;

        return $format ? Money::formatInNairaAsArray($amount) : $amount;
    }

    public function getOriginalAmount(): float
    {
        return (float) $this->model->original_amount;
    }

    public function getProfit(): float
    {
        return (float) $this->model->profit;
    }

    public function getStatus(): string
    {
        return $this->model->status;
    }

    public function getPlanDays(): int
    {
        return $this->model->plan_days;
    }

    public function getPlanPercentage(): int
    {
        return $this->model->plan_percentage;
    }

    /**
     * @return string|null
     */
    public function getPlanName()
    {
        return $this->model->plan_name;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->model->name;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->model->created_at;
    }

    /**
     * @return Carbon|null
     */
    public function getStartedAt()
    {
        return $this->model->started_at;
    }

    /**
     * @return Carbon|null
     */
    public function getMaturityDate()
    {
        return $this->model->maturity_date;
    }

    /**
     * @return Carbon|null
     */
    public function getEndedAt()
    {
        return $this->model->ended_at;
    }

    /**
     * @return Carbon|null
     */
    public function getLiquidatedAt()
    {
        return $this->model->liquidated_at;
    }

    /**
     * @return int|null
     */
    public function getLiquidatedOnDay()
    {
        return (int) $this->model->liquidated_on_day;
    }

    /**
     * @return null|string
     */
    public function getLiquidationReason()
    {
        return $this->model->liquidation_reason;
    }

    /**
     * @return string|null
     */
    public function getLiquidatedBy()
    {
        return $this->model->liquidated_by;
    }

    public function getActiveDaysCount(): int
    {
        $now = now();

        if (! $this->isStarted() OR $this->getStartedAt()->greaterThan($now)) {
            return 0;
        }

        if ($this->isLiquidated()) {
            return $this->getLiquidatedOnDay();
        }

        if ($this->isEnded()) {
            return $this->getStartedAt()->diffInDays($this->getEndedAt());
        }

        return $this->getStartedAt()->diffInDays($now);
    }

    public function isStarted(): bool
    {
        return !! $this->model->started_at;
    }

    public function isEnded(): bool
    {
        return !! $this->model->ended_at;
    }

    public function isLiquidated(): bool
    {
        return !! $this->model->liquidated_at;
    }

    public function isPaidOut(): bool
    {
        return !! $this->model->paid_out_at;
    }

    public function isActive(): bool
    {
        return $this->getStatus() === static::STATUS_ACTIVE;
    }

    public function buildProfitPayments(bool $deleteProfitPayments = false)
    {
        return InvestmentProfitPaymentBuilder::build($this, $deleteProfitPayments);
    }

    public function start(Carbon $startedAt = null, bool $deleteProfitPayments = false)
    {
        $startedAt = $startedAt ?: now();

        return DB::transaction(function() use ($startedAt, $deleteProfitPayments) {

            $this->model->started_at = $startedAt;
            $this->model->maturity_date = $startedAt->copy()->addDays($this->getPlanDays());

            $this->buildProfitPayments($deleteProfitPayments);

            $this->model->status = static::STATUS_ACTIVE;
            $this->model->save();

            return true;
        });
    }

    public function end(): self
    {

        DB::transaction(function() {
            $this->model->ended_at = now();
            $this->model->status = static::STATUS_ENDED;
            $this->model->save();

            $this->setPayout($this->getCalculation()->calculatePayoutAmount(), static::PAYOUT_STATUS_SCHEDULED);

            $this->getUser()->calculateInvestBalance();

            return true;
        });

        return $this;
    }

    public function setLiquidated(int $liquidatedOnDay, string $reason = '', User $by): bool
    {
        $this->model->status = static::STATUS_LIQUIDATED;
        $this->model->liquidated_on_day = $liquidatedOnDay;
        $this->model->liquidation_reason = $reason;
        $this->model->liquidated_at = now();
        $this->model->liquidated_by = $by->getId();

        if ($this->isStarted()) {
            $this->model->ended_at = now();
        }

        return $this->model->save();
    }

    public function getPartialLiquidations(bool $reload = false): PartialLiquidationCollection
    {
        if (! $this->partialLiquidations OR $reload) {
            $this->partialLiquidations = PartialLiquidationCollection::findByInvestmentId($this->getId());
        }

        return $this->partialLiquidations;
    }

    public function hasPartialLiquidationsAfterStart(): bool
    {
        return !! PartialLiquidationCollection::findLiquidatedAfterStartByInvestmentId($this->getId())->count();
    }

    public function partialLiquidation(float $amount, string $reason = '')
    {
        return InvestmentLiquidation::liquidate($this, $amount, $reason);
    }

    /**
     * @param float $amount
     * @return bool
     * @throws \Exception
     */
    public function deductAmount(float $amount): bool
    {
        if ($this->model->amount < $amount) {
            throw new \Exception('Deduct amount is greater than Investment amount');
        }

        $this->model->amount -= $amount;
        $this->model->save();

        $this->getUser()->calculateInvestBalance();

        return true;
    }

    public function transform(array $scopes = []): array
    {
        return InvestmentTransformer::transform($this, $scopes);
    }

    public function getPenaltyPercentage(): int
    {
        return $this->settings->get('investment_liquidation_penalty', 40);
    }

    public function getPayoutAmount(): float
    {
        return floatval($this->model->payout_amount);
    }

    public function getProfitPayoutType()
    {
        return $this->model->profit_payout_type;
    }

    public function isProfitPayoutTypeSingle(): bool
    {
        return $this->getProfitPayoutType() === self::PROFIT_PAYOUT_TYPE_SINGLE;
    }

    public function isProfitPayoutTypeMonthly(): bool
    {
        return $this->getProfitPayoutType() === self::PROFIT_PAYOUT_TYPE_MONTHLY;
    }

    public function getProfitPayments(): ProfitPaymentCollection
    {
        return ProfitPaymentCollection::findByInvestmentId($this->getId());
    }

    public function getPaidProfitPayments(): ProfitPaymentCollection
    {
        return ProfitPaymentCollection::findPaidByInvestmentId($this->getId());
    }

    public function getScheduledProfitPayments(): ProfitPaymentCollection
    {
        return ProfitPaymentCollection::findScheduledByInvestmentId($this->getId());
    }

    /**
     * @param Carbon|null $date
     * @return ProfitPayment|null
     */
    public function findProfitPaymentByPeriodDate(Carbon $date = null): ?ProfitPayment
    {
        $date = $date ?: now();

        $paymentModel = ProfitPaymentModel::where('investment_id', $this->getId())
            ->where('period_start', '<=', $date)
            ->where('period_end', '>', $date)
            ->first();

        if (! $paymentModel) {
            return null;
        }

        return new ProfitPayment($paymentModel);
    }

    public function findScheduledProfitPaymentsFromDate(Carbon $date = null): ProfitPaymentCollection
    {
        return ProfitPaymentCollection::findScheduledByInvestmentIdAndFromDate($this->getId(), $date);
    }

    public function deleteScheduledProfitPayments(array $exceptIds = []): int
    {
        return ProfitPaymentCollection::deleteScheduledByInvestmentId($this->getId(), $exceptIds);
    }

    public function hasPaidProfitPayments(): bool
    {
        return !! $this->getPaidProfitPayments()->count();
    }

    public function hasProfitPayments(): bool
    {
        return !! $this->getProfitPayments()->count();
    }

    public function deleteProfitPayments(): bool
    {
        return ProfitPaymentCollection::deleteByInvestmentId($this->getId());
    }

    public function getCalculation(): InvestmentCalculation
    {
        return $this->calculation;
    }

    public function edit(array $data = []): bool
    {
        if ($this->hasPaidProfitPayments()) {
            throw new \Exception('Investment has a paid profit payments. You can`t change it.');
        }

        if ($planName = array_get($data, 'plan_name')) {
            $this->model->plan_name = $planName;
        }

        if ($planDays = array_get($data, 'plan_days')) {
            $this->model->plan_days = $planDays;
            $this->model->maturity_date = $this->getStartedAt() ? $this->getStartedAt()->copy()->addDays($planDays) : null;
        }

        if ($planPercentage = array_get($data, 'plan_percentage')) {
            $this->model->plan_percentage = $planPercentage;
        }

        if ($profitPayoutType = array_get($data, 'profit_payout_type')) {
            $this->model->profit_payout_type = $profitPayoutType;
        }

        if ($this->isStarted()) {
            $this->buildProfitPayments(true);
        }

        return $this->model->save();
    }

    public function editWithholdingTax(int $value): bool
    {
        $this->model->withholding_tax_percent = $value;

        return $this->model->save();
    }

    public function isChangeable(): bool
    {
        if ($this->hasPaidProfitPayments()) {
            return false;
        }

        if ($this->isEnded() OR $this->isLiquidated()) {
            return false;
        }

        if ($this->hasPartialLiquidationsAfterStart()) {
            return false;
        }

        return true;
    }

    public function setPayout(float $amount, string $status): bool
    {
        $this->model->payout_amount = $amount;
        $this->model->payout_status = $status;

        return $this->model->save();
    }

    public function setPayoutAmount(float $amount): bool
    {
        $this->model->payout_amount = $amount;

        return $this->model->save();
    }

    public function setPayoutStatus(string $status): bool
    {
        $this->model->payout_status = $status;

        return $this->model->save();
    }

    public function failedToPayout($payload = null): bool
    {
        $this->model->payout_status = static::PAYOUT_STATUS_FAILED;
        $this->model->payout_response = serialize($payload);

        return $this->model->save();
    }

    public function successPayout($payload = null): bool
    {
        $this->model->payout_status = static::PAYOUT_STATUS_PAID;
        $this->model->payout_response = serialize($payload);
        $this->model->paid_out_at = now();

        return $this->model->save();
    }

    public function getWithholdingTaxPercent(): int
    {
        return (int) $this->model->withholding_tax_percent;
    }

    public function getAllLogs(): Collection
    {
        return $this->model->logs()
            ->with('createdBy')
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getPublicLogs(): Collection
    {
        return $this->model->logs()
            ->where('is_hidden', false)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function publicLog(array $data)
    {
        $data['is_hidden'] = false;

        return $this->log($data);
    }

    public function hiddenLog(array $data)
    {
        $data['is_hidden'] = true;

        return $this->log($data);
    }

    private function log(array $data)
    {
        $logModel = $this->model->logs()->create([
            'text' => array_get($data, 'text'),
            'payload' => serialize(array_get($data, 'payload')),
            'ip' => array_get($data, 'ip'),
            'is_hidden' => array_get($data, 'is_hidden', false),
            'created_by' => array_get($data, 'created_by'),
        ]);

        return $logModel;
    }
}