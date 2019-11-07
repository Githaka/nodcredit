<?php

namespace App\NodCredit\Investment\Models;

use App\BaseModel;
use App\User;

class InvestmentModel extends BaseModel
{

    protected $table = 'investments';

    protected $fillable = [
        'user_id',
        'amount',
        'original_amount',
        'plan_name',
        'plan_value',
        'plan_days',
        'plan_percentage',
        'started_at',
        'ended_at',
        'maturity_date',
        'liquidated_at',
        'liquidated_on_day',
        'liquidated_by',
        'liquidation_reason',
        'paid_out_at',
        'paid_out_amount',
        'profit',
        'profit_payout_type',
        'withholding_tax_percent',
        'created_by',
        'payment_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'original_amount' => 'float',
        'paid_out_amount' => 'float',
        'profit' => 'float',
        'plan_days' => 'integer',
        'plan_percentage' => 'integer',
        'withholding_tax_percent' => 'integer',
    ];

    protected $dates = [
        'started_at',
        'ended_at',
        'maturity_date',
        'liquidated_at',
        'paid_out_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function liquidatedBy()
    {
        return $this->belongsTo(User::class, 'liquidated_by');
    }

    public function partialLiquidations()
    {
        return $this->hasMany(PartialLiquidationModel::class, 'investment_id');
    }

    public function profitPayments()
    {
        return $this->hasMany(ProfitPaymentModel::class, 'investment_id');
    }

    public function logs()
    {
        return $this->hasMany(InvestmentLogModel::class, 'investment_id');
    }
}
