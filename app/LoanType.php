<?php

namespace App;


class LoanType extends BaseModel
{
    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

   public function applications()
   {
       return $this->hasMany(LoanApplication::class);
   }
}
