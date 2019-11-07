<?php

namespace App\Listeners;

use App\Events\LoanApplicationDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleLoanApplicationDeleted
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
     * @param  LoanApplicationDeleted  $event
     * @return void
     */
    public function handle(LoanApplicationDeleted $event)
    {

        $event->loan->payments()->delete();
        // lets check how may loan rejections this user has
        $event->loan->owner->getScoreInfo('LOAN_REJECTED', $event->loan->owner->applications()->where('status', 'rejected')->count());

    }
}
