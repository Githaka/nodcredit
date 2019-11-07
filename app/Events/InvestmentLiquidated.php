<?php

namespace App\Events;

use App\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InvestmentLiquidated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $investment;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Payment $investment)
    {
        $this->investment = $investment;
    }


}
