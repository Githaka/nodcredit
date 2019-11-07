<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartPayment extends BaseModel
{
    protected $fillable = ['amount'];

    public function payment()
    {
        return $this->belongsTo(LoanPayment::class);
    }
}
