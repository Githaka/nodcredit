<?php

namespace App\Events;

use App\LoanPayment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LoanRepayment
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payment;


    public function __construct(LoanPayment $payment)
    {
        $this->payment = $payment;
    }


    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
