<?php

namespace App;


class Token extends BaseModel
{

    protected $table = 'tokens';

    protected $fillable = [
        'user_id',
        'expire_at',
        'token'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'expire_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
