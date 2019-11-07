<?php

namespace App\NodCredit\Investment\Models;

use App\BaseModel;
use App\User;

class PartialLiquidationModel extends BaseModel
{

    protected $table = 'investment_partial_liquidations';

    protected $fillable = [
        'investment_id',
        'status',
        'amount',
        'profit',
        'reason',
        'penalty_amount',
        'penalty_percent',
        'liquidated_on_day',
        'created_by',
        'paid_out_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'profit' => 'float',
        'penalty_amount' => 'float',
        'penalty_percent' => 'integer',
        'liquidated_on_day' => 'integer',
    ];

    protected $dates = [
        'paid_out_at',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function investment()
    {
        return $this->belongsTo(InvestmentModel::class, 'investment_id');
    }

}
