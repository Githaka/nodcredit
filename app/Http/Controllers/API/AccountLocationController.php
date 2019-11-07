<?php

namespace App\Http\Controllers\API;

use App\NodCredit\Account\Location\UserLocation;
use App\NodCredit\Account\User;
use Illuminate\Http\Request;

class AccountLocationController extends ApiController
{

    public function addLocation(Request $request, User $accountUser)
    {
        $lat = floatval($request->json('latitude'));
        $lon = floatval($request->json('longitude'));

        if (! $lat OR ! $lon) {
            return $this->errorResponse('latitude and longitude are required');
        }

        try {
            UserLocation::create(
                $accountUser->getId(),
                $lat,
                $lon,
                $request->ip()
            );
        }
        catch (\Exception $exception) {
            return $this->errorResponse('latitude and longitude are required');
        }

        return $this->successResponse('OK');
    }


}