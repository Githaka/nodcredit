<?php

namespace App;

class Payment extends BaseModel
{
    protected $dates = [
            'created_at',
            'updated_at',
            'investment_started',
            'investment_ended',
    ];

    protected $fillable = [
        'amount',
        'user_id',
        'reason',
        'status',
        'payment_reference',
        'is_investment',
        'investment_tenor',
        'investment_started',
        'investment_ended'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeInvestments($query)
    {
        return $query->where('is_investment', true)->where('status', 'success');
    }

    public function scopeNotPaid($query) {
        return $query->whereNull('profit_paid_at');
    }

    public function scopeLiquidatedByUser($query) {
        return $query->whereNotNull('liquidated_by');
    }

    public function scopeStartedInvestments($query)
    {
        return $query->whereNotNull('investment_started');
    }

    public function scopeEndedInvestments($query)
    {
        return $query->whereNotNull('investment_ended');
    }

    public function getInvestmentTenorAttribute($value)
    {
        return $value; //TODO: I HAVE PLAN FOR THIS
    }

    public function calculateLiquidatedProfit()
    {
        if($this->liquidated_at && !$this->profit_paid_at) {

            $total = $this->amount + $this->investment_profit; // the principal amount + profit

            $maxPayOut = doubleval(Setting::v('max_auto_liquidation_amount')); // maximum allowed to pay out automatically
            if($total > $maxPayOut) {
                return 0; // We can not payout this amount
            }

            // check if we are taking out some percentage
            $percentageToDeduct = $this->investment_profit > 0 ? ($this->investment_profit / 100) * doubleval(Setting::v('liquidation_charge_percentage')) : 0;

            return $total - $percentageToDeduct;

        }


        return 0;

    }


    public function getInvestmentSetting()
    {
        $tenorSettings = json_decode(Setting::v('investmentConfig'));
        $selectedTenor = null;
        foreach($tenorSettings as $setting)
        {
            if($setting->value === $this->investment_tenor)
            {
                $selectedTenor = $setting;
                break;
            }
        }

        return $selectedTenor;
    }

    public function calculatePossibleProfit()
    {
        $tenorSettings = json_decode(Setting::v('investmentConfig'));
        $selectedTenor = null;
        foreach($tenorSettings as $setting)
        {
            if($setting->value === $this->investment_tenor)
            {
                $selectedTenor = $setting;
                break;
            }
        }

        if(!$selectedTenor || !$this->investment_started) return 0;


        $yearlyInterest = ($this->amount / 100) * $selectedTenor->percentage;
        $profit = $yearlyInterest / 365;
        $days = $this->investment_started->diffInDays(now());
        return $profit * $days;
    }

    /**
     * This method calculates liquidation of the investment then liquidates it
     * It must only be using for liquidating an investment
     *
     * @param $reason string
     *
     * @return bool
     */
    public function calculateLiquidation($reason, $liquidatedBy=null)
    {
        $this->investment_liquidation_reason = $reason;
        $this->investment_ended = now();

        if(!$this->investment_started)
        {
            // no profit should be calculated if investment has not started
            $this->save();
            return true;
        }

        $this->investment_profit = $this->calculatePossibleProfit();
        $this->liquidated_by = $liquidatedBy;
        $this->liquidated_at = now();
        $this->liquidation_days = $this->investment_started->diffInDays( $this->liquidated_at);

        $this->save();
        return true;
    }

    public function getAmount()
    {
        return 'NGN ' . number_format($this->amount);
    }
}
