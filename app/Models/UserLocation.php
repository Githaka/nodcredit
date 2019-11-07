<?php

namespace App\Models;

use App\BaseModel;
use App\User;

class UserLocation extends BaseModel
{

    protected $fillable = [
        'user_id',
        'lat',
        'lon',
        'geocode_results',
        'geocode_status',
        'ip',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
