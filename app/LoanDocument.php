<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LoanDocument extends BaseModel
{

    protected $hidden = ['created_at', 'deleted_at', 'updated_at', 'loan_application_id', 'path'];

    protected $fillable = [
        'loan_application_id',
        'path',
        'document_type',
        'description',
        'document_extension'
    ];

    protected $dates = [
        'parser_sent_at'
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'unlock_attempts' => 'integer'
    ];

    public function loan()
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function doc_type()
    {
        return $this->belongsTo(LoanDocumentType::class, 'document_type');
    }

    public function getFullpath(): string
    {
        return Storage::disk('documents')->getDriver()->getAdapter()->applyPathPrefix($this->path);
    }

}
