<?php

namespace App\Models;

use App\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserContactEmail extends BaseModel
{

    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'email',
    ];

    public function contact()
    {
        return $this->belongsTo(UserContact::class, 'contact_id');
    }

}
