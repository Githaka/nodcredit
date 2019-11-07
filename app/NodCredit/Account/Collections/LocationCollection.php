<?php

namespace App\NodCredit\Account\Collections;

use App\Models\UserLocation as Model;
use App\NodCredit\Account\Location\UserLocation;
use App\NodCredit\Helpers\BaseCollection;
use Illuminate\Database\Eloquent\Collection;

class LocationCollection extends BaseCollection
{

    public static function findByUserId(string $id): self
    {
        $models = Model::where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function findNewForGeocode(): self
    {
        $models = Model::where('geocode_status', UserLocation::GEOCODE_STATUS_NEW)
            ->orderBy('created_at', 'DESC')
            ->get();

        return static::makeCollectionFromModels($models);
    }

    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new UserLocation($model));
        }

        return $collection;
    }

    /**
     * @return UserLocation[]
     */
    public function all(): array
    {
        return parent::all();
    }

    public function push(UserLocation $location): self
    {
        $this->items->push($location);

        return $this;
    }

}