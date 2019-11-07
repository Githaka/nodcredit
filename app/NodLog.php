<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NodLog extends BaseModel
{
    protected $fillable = ['user_id', 'subject', 'message'];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public static function write($user, $subject, $message = null)
    {
        $userId = $user ? ($user instanceof User ? $user->id : $user) : null;
        return static::create(['user_id' => $userId, 'subject' => $subject, 'message' => $message]);
    }
}
