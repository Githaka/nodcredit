<?php

namespace App;


class SMSLog extends BaseModel
{
    protected $table = 'sms_logs';

    protected $fillable = ['phone', 'message', 'response_id', 'response_message'];
}
