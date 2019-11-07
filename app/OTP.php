<?php

namespace App;

class OTP extends BaseModel
{

    protected $table = 'o_t_ps';

    protected $fillable = ['phone', 'otp', 'expire_at'];
}
