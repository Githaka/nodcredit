<?php

namespace App\NodCredit\Investment;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Models\PartialLiquidationModel;
use App\NodCredit\Investment\Transformers\PartialLiquidationTransformer;
use Carbon\Carbon;

class PartialLiquidation
{

    const STATUS_NEW = 'new';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';

    /**
     * @var PartialLiquidationModel
     */
    private $model;

    /**
     * @param string $id
     * @return null|static
     */
    public static function find(string $id)
    {
        $model = PartialLiquidationModel::find($id);

        if (! $model) {
            return null;
        }

        return new static($model);
    }

    /**
     * PartialLiquidation constructor.
     * @param PartialLiquidationModel $model
     */
    public function __construct(PartialLiquidationModel $model)
    {
        $this->model = $model;
    }


    public function getId(): string
    {
        return $this->model->id;
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

    public function getProfit(): float
    {
        return $this->model->profit;
    }

    public function getStatus(): string
    {
        return $this->model->status;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->model->created_at;
    }

    public function getReason()
    {
        return $this->model->reason;
    }

    public function getLiquidatedOnDay(): int
    {
        return $this->model->liquidated_on_day;
    }

    public function getProfitPerDay(): float
    {
        $value = number_format($this->getProfit() / $this->getLiquidatedOnDay(), 2, '.', '');

        return floatval($value);
    }

    public function getPenaltyAmount(): float
    {
        return floatval($this->model->penalty_amount);
    }

    public function getPenaltyPercent(): int
    {
        return $this->model->penalty_percent;
    }

    public function getInvestment(): Investment
    {
        return Investment::find($this->getInvestmentId());
    }

    public function getInvestmentId(): string
    {
        return $this->model->investment_id;
    }

    public function transform(array $scopes = []): array
    {
        return PartialLiquidationTransformer::transform($this, $scopes);
    }

    /**
     * @return Carbon|null
     */
    public function getPaidOutAt(): ?Carbon
    {
        return $this->model->paid_out_at;
    }

    public function isPaid(): bool
    {
        return !! $this->getPaidOutAt();
    }

    public function paid($payload = null): bool
    {
        $this->model->status = static::STATUS_PAID;
        $this->model->paid_out_at = now();
        $this->model->paid_out_payload = serialize($payload);

        return $this->model->save();
    }

}