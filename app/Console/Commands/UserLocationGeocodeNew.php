<?php

namespace App\Console\Commands;

use App\NodCredit\Account\Collections\LocationCollection;
use App\NodCredit\Account\Location\UserLocation;
use Illuminate\Console\Command;

class UserLocationGeocodeNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-location:geocode-new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find addresses using coordinates';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $locations = LocationCollection::findNewForGeocode();

        /** @var UserLocation $location */
        foreach ($locations->all() as $location) {
            $location->geocodeFromCoordinates();
        }
    }
}
