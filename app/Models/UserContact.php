<?php

namespace App\Models;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserContact extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'external_id',
        'user_id',
        'name',
        'starred',
        'in_visible_group',
        'payload',
    ];

    protected $casts = [
        'starred' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function emails()
    {
        return $this->hasMany(UserContactEmail::class, 'contact_id', 'id');
    }

    public function phones()
    {
        return $this->hasMany(UserContactPhone::class, 'contact_id', 'id');
    }

}
