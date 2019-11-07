<?php

namespace App\NodCredit\Investment\Models;

use App\BaseModel;
use App\User;

class ProfitPaymentModel extends BaseModel
{

    protected $table = 'investment_profit_payments';

    protected $fillable = [
        'investment_id',
        'status',
        'amount',
        'payout_amount',
        'scheduled_at',
        'period_days',
        'period_start',
        'period_end',
        'auto_payout',
        'withholding_tax_amount',
        'withholding_tax_percent'
    ];

    protected $casts = [
        'amount' => 'float',
        'payout_amount' => 'float',
        'liquidations_profit' => 'float',
        'auto_payout' => 'boolean',
        'period_days' => 'integer',
        'withholding_tax_amount' => 'float',
        'withholding_tax_percent' => 'integer',
    ];

    protected $dates = [
        'paid_out_at',
        'scheduled_at',
        'period_start',
        'period_end',
    ];

    public function investment()
    {
        return $this->belongsTo(InvestmentModel::class, 'investment_id');
    }

}
