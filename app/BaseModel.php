<?php 

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Foundation\Auth\User as Authenticatable;

class BaseModel extends Authenticatable {

    use Notifiable, Uuids;

    public $incrementing = false;
}
