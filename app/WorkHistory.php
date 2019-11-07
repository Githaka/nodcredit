<?php

namespace App;

class WorkHistory extends BaseModel
{

    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'user_id'];

    protected $fillable = [
        'employer_name',
        'work_industry',
        'work_address',
        'work_phone',
        'work_email',
        'work_website',
        'is_current',
        'started_date',
        'stopped_date'
    ];

    public static $allowFields = [];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        static::$allowFields = $this->fillable;
    }


    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
