<?php

namespace App\NodCredit\Investment;

use App\NodCredit\Account\User;
use App\NodCredit\Investment\Models\ProfitPaymentModel;
use App\NodCredit\Investment\Transformers\ProfitPaymentTransformer;
use Carbon\Carbon;

class ProfitPayment
{
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';

    /** @var Investment */
    private $investment;

    /** @var User */
    private $user;

    /** @var ProfitPaymentModel */
    private $model;


    /**
     * @param string $id
     * @return null|static
     */
    public static function find(string $id)
    {
        $model = ProfitPaymentModel::find($id);

        if (! $model) {
            return null;
        }

        return new static($model);
    }

    public function __construct(ProfitPaymentModel $model)
    {
        $this->model = $model;
    }

    public function getId(): string
    {
        return $this->model->id;
    }

    public function getStatus(): string
    {
        return $this->model->status;
    }

    public function getAmount(): float
    {
        return floatval($this->model->amount);
    }

    public function getPayoutAmount(): float
    {
        return (float) $this->model->payout_amount;
    }

    public function getWithholdingTaxPercent(): int
    {
        return (int) $this->model->withholding_tax_percent;
    }

    public function getWithholdingTaxAmount(): float
    {
        return (float) $this->model->withholding_tax_amount;
    }

    public function getPeriodStart(): Carbon
    {
        return $this->model->period_start;
    }

    public function getPeriodEnd(): Carbon
    {
        return $this->model->period_end;
    }

    public function getPeriodDays(): int
    {
        return (int) $this->model->period_days;
    }

    public function getScheduledAt(): Carbon
    {
        return $this->model->scheduled_at;
    }

    public function getAutoPayout()
    {
        return $this->model->auto_payout;
    }

    public function getPaidAt()
    {
        return $this->model->paid_at;
    }

    public function isScheduled(): bool
    {
        return $this->getStatus() === static::STATUS_SCHEDULED;
    }

    public function isPaid(): bool
    {
        return $this->getStatus() === static::STATUS_PAID;
    }

    public function isPayable(): bool
    {
        if ($this->isPaid()) {
            return false;
        }

        $now = now();

        if ($this->getScheduledAt()->gt($now)) {
            return false;
        }

        return true;
    }

    public function getPaidPayload()
    {
        return $this->model->paid_payload;
    }

    public function getLiquidationsProfit(): float
    {
        return floatval($this->model->liquidations_profit);
    }

    public function getInvestmentId(): string
    {
        return $this->model->investment_id;
    }

    public function getInvestment(): Investment
    {
        if (! $this->investment) {
            $this->investment = Investment::find($this->getInvestmentId());
        }

        return $this->investment;
    }

    public function getUser(): User
    {
        if (! $this->user) {
            $this->user = $this->getInvestment()->getUser();
        }

        return $this->user;
    }

    public function setAutoPayout(bool $value): bool
    {
        if (! $this->isScheduled()) {
            throw new \Exception("Only scheduled payments can be changed");
        }

        $this->model->auto_payout = $value;

        return $this->model->save();
    }

    public function failed($payload = null): bool
    {
        $this->model->status = static::STATUS_FAILED;
        $this->model->payout_response = serialize($payload);

        return $this->model->save();
    }

    public function paid($payload = null): bool
    {
        $this->model->status = static::STATUS_PAID;
        $this->model->payout_response = serialize($payload);
        $this->model->paid_out_at = now();

        return $this->model->save();
    }

    public function transform(array $scopes = []): array
    {
        return ProfitPaymentTransformer::transform($this, $scopes);
    }

    public function fullLiquidationUpdate(float $amount, Carbon $end = null): bool
    {
        $end = $end ?: now();

        $this->model->amount = $amount;
        $this->model->period_end = $end;
        $this->model->period_days = $this->getPeriodStart()->diffInDays($end);
        $this->model->scheduled_at = $end->copy()->addDay();

        return $this->model->save();
    }

    public function updatePrincipalProfit(float $amount): bool
    {
        $this->model->amount = $amount + $this->getLiquidationsProfit();

        return $this->model->save();
    }

    public function increaseLiquidationsProfit(float $amount): bool
    {
        $this->model->liquidations_profit += $amount;

        return $this->model->save();
    }

    public function editWithholdingTax(int $taxPercent): bool
    {
        $taxAmount = floatval($this->getAmount() * $taxPercent / 100);

        $this->model->withholding_tax_percent = $taxPercent;
        $this->model->withholding_tax_amount = $taxAmount;
        $this->model->payout_amount = floatval($this->getAmount() - $taxAmount);

        return $this->model->save();
    }

}