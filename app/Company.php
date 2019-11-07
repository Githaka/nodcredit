<?php

namespace App;

class Company extends BaseModel
{
    protected $fillable = ['name','interest_rate_flat_monthly','tenor_per_month','max_loan_amount','length_of_service'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

   public function applications()
   {
       return $this->hasMany(LoanApplication::class);
   }
}
