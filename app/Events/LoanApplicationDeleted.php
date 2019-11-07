<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\LoanApplication;

/**
 * This event is fired when a loan is reject also
 *
 * Class LoanApplicationDeleted
 *
 * @package App\Events
 */
class LoanApplicationDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  $loan;


    public function __construct(LoanApplication $loanApplication)
    {
        $this->loan = $loanApplication;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
