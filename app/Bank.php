<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bank extends BaseModel
{
    protected $fillable = ['code', 'name'];

    protected $hidden = ['created_at', 'updated_at'];
}
