<?php

namespace App;

class LoanDocumentType extends BaseModel
{
    protected $fillable = ['name'];


    public function loans()
    {
        return $this->hasMany(LoanApplication::class, 'document_type', 'id');
    }
}
