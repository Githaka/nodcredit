<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['email', 'token', 'created_at'];

    protected $table = 'password_resets';

    public static function createEntry(array $data)
    {
        static::where('email', $data['email'])->delete();
        return static::create($data);
    }

    public static function cleanUp($email)
    {
        static::where('email', $email)->delete();
    }
}
