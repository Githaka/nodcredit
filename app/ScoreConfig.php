<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScoreConfig extends Model
{
    protected $guarded = ['id'];

    public function getFrequenciesAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }
}
