<?php

namespace App\Models;

use App\BaseModel;

class FaqItem extends BaseModel
{
    protected $fillable = [
        'title',
        'text',
        'category',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort' => 'integer'
    ];

}
