<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use Uuids;

    public $incrementing = false;

    protected $guarded = ['id'];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
