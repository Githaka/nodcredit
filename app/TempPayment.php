<?php

namespace App;

class TempPayment extends BaseModel
{
    protected $fillable = [
        'amount', 'month', 'sess', 'loan_amount', 'interest', 'tenor'
    ];
}
