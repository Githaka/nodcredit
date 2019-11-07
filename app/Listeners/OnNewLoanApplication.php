<?php

namespace App\Listeners;

use App\Events\NewLoanApplication;
use App\Mail\NewLoanApplicationAdminNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class OnNewLoanApplication implements ShouldQueue
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
     * @param  NewLoanApplication  $event
     * @return void
     */
    public function handle(NewLoanApplication $event)
    {
        $to = config('nodcredit.mail_to.new_loans');

        Mail::to($to)->send(new NewLoanApplicationAdminNotification($event->loan));
    }
}
