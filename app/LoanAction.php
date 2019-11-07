<?php

namespace App;


class LoanAction extends BaseModel
{
    protected $fillable = ['action', 'finger_print', 'loan_application_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loan()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

}
