<?php namespace App;

use Zizaco\Entrust\EntrustRole;
use Illuminate\Notifications\Notifiable;
use Webpatser\Uuid\Uuid;

class Role extends EntrustRole
{
    use Notifiable;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }
}