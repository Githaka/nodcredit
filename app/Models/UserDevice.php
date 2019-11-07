<?php

namespace App\Models;

use App\BaseModel;
use App\User;

class UserDevice extends BaseModel
{

    protected $fillable = [
        'user_id',
        'device_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
