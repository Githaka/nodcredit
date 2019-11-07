<?php

namespace App\Listeners;

use App\Events\UserUpdatedProfile;


class HandleUserProfileUpdate
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
     * @param  UserUpdatedProfile  $event
     * @return void
     */
    public function handle(UserUpdatedProfile $event)
    {
        $event->user->validateSuccessfullyAddedDetailsScore();
    }
}
