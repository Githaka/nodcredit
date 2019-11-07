<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GiveScore
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $points;
    public $reason;

    public function __construct(User $user, $points, $reason='unknown')
    {
        $this->user = $user;
        $this->points = $points;
        $this->reason = $reason;
    }

}
