<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends BaseModel
{
    protected $fillable = ['loan_payment_id', 'info'];
}
