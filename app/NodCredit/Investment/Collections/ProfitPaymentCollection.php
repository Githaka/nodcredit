<?php

namespace App\NodCredit\Investment\Collections;

use App\NodCredit\Contracts\Transformable;
use App\NodCredit\Investment\Models\ProfitPaymentModel as Model;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Investment\ProfitPayment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProfitPaymentCollection extends BaseCollection implements Transformable
{

    public static function findScheduledInHours(int $hours = 24): self
    {
        $hourStart = now()->addHours($hours)->startOfHour();
        $hourEnd = now()->addHours($hours)->endOfHour();

        $models = Model::where('status', ProfitPayment::STATUS_SCHEDULED)
            ->whereNull('paid_out_at')
            ->whereBetween('scheduled_at', [$hourStart, $hourEnd])
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findForAutoPayout(): self
    {
        $now = now();

        $models = Model::where('auto_payout', true)
            ->where('status', ProfitPayment::STATUS_SCHEDULED)
            ->whereNull('paid_out_at')
            ->where('scheduled_at', '<', $now)
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findScheduledByInvestmentId(string $id): self
    {
        $models = Model::where('investment_id', $id)
            ->where('status', ProfitPayment::STATUS_SCHEDULED)
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findScheduledByInvestmentIdAndFromDate(string $id, Carbon $date = null): self
    {
        $date = $date ?: now();

        $models = Model::where('investment_id', $id)
            ->where('period_end', '>', $date)
            ->where('status', ProfitPayment::STATUS_SCHEDULED)
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function deleteScheduledByInvestmentId(string $id, array $exceptIds = []): int
    {
        $builder = Model::where('investment_id', $id)->where('status', ProfitPayment::STATUS_SCHEDULED);

        if (count($exceptIds)) {
            $builder->whereNotIn('id', $exceptIds);
        }

        return $builder->delete();
    }

    public static function findByInvestmentId(string $id): self
    {
        $models = Model::where('investment_id', $id)
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findPaidByInvestmentId(string $id): self
    {
        $models = Model::where('investment_id', $id)
            ->where('status', ProfitPayment::STATUS_PAID)
            ->orderBy('scheduled_at', 'ASC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function deleteByInvestmentId(string $id): bool
    {
        return Model::where('investment_id', $id)->delete();
    }

    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new ProfitPayment($model));
        }

        return $collection;
    }

    public function sumAmount(): float
    {
        $total = 0;

        foreach ($this->all() as $item) {
            $total += $item->getAmount();
        }

        return floatval($total);
    }

    public function push(ProfitPayment $payment): self
    {
        $this->items->push($payment);

        return $this;
    }

    public function transform(array $scopes = []): array
    {
        $result = [];

        /** @var ProfitPayment $payment */
        foreach ($this->all() as $payment) {
            $result[] = $payment->transform($scopes);
        }

        return $result;
    }
}