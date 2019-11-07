<?php

namespace App\Mail\Investment;

use App\NodCredit\Account\User;
use App\NodCredit\Investment\Investment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LiquidationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reason;

    /**
     * @var \App\NodCredit\Account\User
     */
    public $user;

    public function __construct(User $user, string $reason = '')
    {
        $this->user = $user;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('emails.users.investment.liquidation')
            ->subject('You Are Liquidating Your Investment');
    }
}
