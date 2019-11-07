<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'k',
        'v',
        'description',
        'type',
        'group'
    ];

    /**
     * Instead of modifying the controller for loan_min  and loan_max
     * I will add an overriding condition here that picks the users loan_min & loan_max based on their score
     */
    public static function v($key)
    {
        $obj = static::where('k', $key)->first();
        if(!$obj) return '';

        if(!auth()->guest())
        {
            $loanRange = auth()->user()->getUserLoanMinMax();
            if($key === 'loan_min') return $loanRange->loan_min;
            if($key === 'loan_max') return $loanRange->loan_max;
        }
        
        return $obj->v;
    }

    public static function put($key, $value)
    {
        $obj = static::where('k', $key)->first();
        if(!$obj)
        {
            return static::create(['k' => $key, 'v' => $value]);
        }

        return $obj;
    }

}
