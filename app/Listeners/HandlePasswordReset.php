<?php

namespace App\Listeners;

use App\Events\OnPasswordResetRequest;
use App\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;

class HandlePasswordReset implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OnPasswordResetRequest  $event
     * @return void
     */
    public function handle(OnPasswordResetRequest $event)
    {
        //$event->user // reset password for this user
        try {
            $token = base64_encode(sprintf('%s::%s', str_random(64), $event->user->email));
            PasswordReset::createEntry(['email' => $event->user->email, 'token' => $token, 'created_at' => now()]);
            Mail::to($event->user)->send(new PasswordResetMail($event->user, $token));
            echo 'OK' . PHP_EOL;
        }
        catch(\Exception $e)
        {
            echo 'ERROR: ' .  $e->getMessage() . PHP_EOL;
        }
    }
}
