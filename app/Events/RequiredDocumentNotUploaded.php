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

class RequiredDocumentNotUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $application;

    public $sendRejected;

    /**
     * RequiredDocumentNotUploaded constructor.
     *
     * @param \App\LoanApplication $application
     * @param bool                 $sendRejected
     */
    public function __construct(LoanApplication $application, $sendRejected=false)
    {
        $this->application = $application;
        $this->sendRejected = $sendRejected;
    }


}
