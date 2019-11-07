<?php

namespace App\Listeners;

use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleGiveScore
{

    public function handle($event)
    {
       if($event->user instanceof User)
       {
           $event->user->giveScore($event->points, $event->reason);
       }
    }
}
