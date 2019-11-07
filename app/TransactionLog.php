<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends BaseModel
{

    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'trans_type',
        'payload',
        'amount',
        'performed_by',
        'status',
        'model',
        'model_id',
        'response_message',
        'gateway_response',
        'pay_for',
        'user_id',
        'card_id',
    ];
    public function getAmount()
    {
        return 'NGN ' . number_format($this->amount);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function card()
    {
        return $this->belongsTo(UserCard::class, 'card_id');
    }
}
