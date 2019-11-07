<?php

namespace App\NodCredit\Investment\Models;

use App\BaseModel;
use App\User;

class InvestmentLogModel extends BaseModel
{

    protected $table = 'investment_logs';

    protected $fillable = [
        'investment_id',
        'created_by',
        'text',
        'payload',
        'ip',
        'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function investment()
    {
        return $this->belongsTo(User::class, 'investment_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
