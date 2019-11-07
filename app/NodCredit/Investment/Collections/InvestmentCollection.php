<?php

namespace App\NodCredit\Investment\Collections;

use App\NodCredit\Helpers\Money;
use App\NodCredit\Investment\Investment;
use App\NodCredit\Investment\Models\InvestmentModel as Model;
use App\NodCredit\Contracts\Transformable;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Settings;
use Illuminate\Support\Collection;

class InvestmentCollection extends BaseCollection implements Transformable
{

    public static function findMatureForCompleting(): self
    {
        $now = now();

        $models = Model::where('status', Investment::STATUS_ACTIVE)
            ->where('maturity_date', '<=', $now)
            ->whereNull('ended_at')
            ->whereNull('liquidated_at')
            ->orderBy('created_at')
            ->get()
        ;

        return static::makeCollectionFromModels($models);
    }

    public static function findCompletedForAutoPayout(): self
    {
        /** @var Settings $settings */
        $settings = app(Settings::class);

        $maxAmount = $settings->get('investment_max_auto_payout', 500000);

        $models = Model::where('status', Investment::STATUS_ENDED)
            ->where('payout_status', Investment::PAYOUT_STATUS_SCHEDULED)
            ->whereNull('paid_out_at')
            ->where('payout_amount', '<=', $maxAmount)
            ->orderBy('created_at')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findLiquidatedForAutoPayout(): self
    {
        /** @var Settings $settings */
        $settings = app(Settings::class);

        $maxAmount = $settings->get('investment_max_auto_payout', 500000);

        $models = Model::where('status', Investment::STATUS_LIQUIDATED)
            ->where('payout_status', Investment::PAYOUT_STATUS_SCHEDULED)
            ->whereNull('paid_out_at')
            ->where('payout_amount', '<=', $maxAmount)
            ->orderBy('created_at')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findAll(): self
    {
        $models = Model::orderBy('created_at', 'DESC')->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findByUserId(string $id): self
    {
        $models = Model::where('user_id', $id)->orderBy('created_at', 'DESC')->get();

        return static::makeCollectionFromModels($models);
    }

    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new Investment($model));
        }

        return $collection;
    }

    /**
     * @param bool $format
     * @return array|float
     */
    public function sumAmount(bool $format = false)
    {
        $total = 0;

        /** @var Investment $item */
        foreach ($this->all() as $item) {
            $total += $item->getOriginalAmount();
        }

        return $format ? Money::formatInNairaAsArray($total) : floatval($total);
    }

    public function push(Investment $investment): self
    {
        $this->items->push($investment);

        return $this;
    }

    public function transform(array $scopes = []): array
    {
        $result = [];

        /** @var Investment $investment */
        foreach ($this->all() as $investment) {
            $result[] = $investment->transform($scopes);
        }

        return $result;
    }

}