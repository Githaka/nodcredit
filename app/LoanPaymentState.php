<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanPaymentState extends BaseModel
{
    protected $fillable = ['user_id', 'loan_application_id', 'loan_payment_id', 'action'];
}
