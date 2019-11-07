<?php

namespace App\Events;

use App\LoanApplication;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RequiredDocumentUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $application;

    public function __construct(LoanApplication $application)
    {
        $this->application = $application;
    }


}
