<?php

namespace App\NodCredit\Account\Location;

use App\Models\UserLocation as Model;
use Carbon\Carbon;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;

class UserLocation
{
    const GEOCODE_STATUS_NEW = 'new';
    const GEOCODE_STATUS_HANDLED = 'handled';

    /**
     * Model
     */
    private $model;

    /**
     * @var StatefulGeocoder
     */
    private $geocoder;

    public static function create(string $userId, float $lat, float $lon, string $ip = null): self
    {
        $model = Model::create([
            'user_id' => $userId,
            'lat' => $lat,
            'lon' => $lon,
            'ip' => $ip,
            'geocode_status' => static::GEOCODE_STATUS_NEW
        ]);

        return new static($model);
    }

    public static function find(string $id)
    {
        $model = Model::find($id);

        if (! $model) {
            return null;
        }

        return new static($model);
    }

    public static function findLastByUserId(string $id)
    {
        $model = Model::where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! $model) {
            return null;
        }

        return new static($model);
    }

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->geocoder = app(StatefulGeocoder::class);
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getId(): string
    {
        return $this->model->id;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt()
    {
        return $this->model->created_at;
    }
    public function getLat()
    {
        return $this->model->lat;
    }

    public function getLon()
    {
        return $this->model->lon;
    }

    public function hasResults(): bool
    {
        return !! $this->getResults();
    }

    public function getGeocodeStatus()
    {
        return $this->model->geocode_status;
    }

    public function isGeocodeStatusNew(): bool
    {
        return $this->getGeocodeStatus() === static::GEOCODE_STATUS_NEW;
    }

    public function isGeocodeStatusHandled(): bool
    {
        return $this->getGeocodeStatus() === static::GEOCODE_STATUS_HANDLED;
    }

    public function getResults()
    {
        return unserialize($this->model->geocode_results);
    }

    public function geocodeFromCoordinates()
    {
        $results = $this->geocoder->reverseQuery(ReverseQuery::fromCoordinates($this->getLat(), $this->getLon()));

        $this->model->geocode_results = serialize($results);
        $this->model->geocode_status = static::GEOCODE_STATUS_HANDLED;
        $this->model->save();

        return $this;
    }
}