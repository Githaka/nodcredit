<?php

namespace App;

class LoanRange extends BaseModel
{
    protected $fillable = ['pay_month', 'min', 'max', 'min_month', 'max_month'];

    protected $hidden = ['created_at', 'updated_at'];

    public static function getByAmount($amount) {

        $output = false;

        $records = static::all();
        foreach($records as $record) {
            if($amount >= $record->min && $amount <= $record->max) {
                $output = $record;
                break;
            }
        }
        return $output;
    }
}
