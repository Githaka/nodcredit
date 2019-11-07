<?php

namespace App\NodCredit\Investment\Collections;

use App\NodCredit\Investment\Models\PartialLiquidationModel as Model;
use App\NodCredit\Contracts\Transformable;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Investment\PartialLiquidation;
use App\NodCredit\Settings;
use Illuminate\Support\Collection;

class PartialLiquidationCollection extends BaseCollection implements Transformable
{

    public static function findForAutoPayout(): self
    {
        /** @var Settings $settings */
        $settings = app(Settings::class);

        $maxAmount = $settings->get('investment_max_auto_payout', 500000);

        $models = Model::where('status', PartialLiquidation::STATUS_NEW)
            ->whereNull('paid_out_at')
            ->where('amount', '<=', $maxAmount)
            ->orderBy('created_at')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findLiquidatedAfterStartByInvestmentId(string $id): self
    {
        $models = Model::where('investment_id', $id)
            ->where('liquidated_on_day', '>', 0)
            ->orderBy('created_at', 'DESC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findByInvestmentId(string $id): self
    {
        $models = Model::where('investment_id', $id)->orderBy('created_at', 'DESC')->get();

        return static::makeCollectionFromModels($models);
    }


    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new PartialLiquidation($model));
        }

        return $collection;
    }

    public function sumProfit(): float
    {
        $total = 0;

        /** @var PartialLiquidation $item */
        foreach ($this->all() as $item) {
            $total += $item->getProfit();
        }

        return floatval($total);
    }

    public function sumPenalties(): float
    {
        $total = 0;

        /** @var PartialLiquidation $item */
        foreach ($this->all() as $item) {
            $total += $item->getPenaltyAmount();
        }

        return floatval($total);
    }

    public function push(PartialLiquidation $partialLiquidation): self
    {
        $this->items->push($partialLiquidation);

        return $this;
    }

    public function transform(array $scopes = []): array
    {
        $result = [];

        /** @var PartialLiquidation $partialLiquidation */
        foreach ($this->all() as $partialLiquidation) {
            $result[] = $partialLiquidation->transform($scopes);
        }

        return $result;
    }

}