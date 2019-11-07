<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SendMessage' => [
            'App\Listeners\OnSendMessage',
        ],
        'App\Events\LoanRepayment' => [
            'App\Listeners\OnLoanRepayment',
        ],

        'App\Events\NewLoanApplication' => [
            'App\Listeners\OnNewLoanApplication',
        ],

        'App\Events\OnPasswordResetRequest' => [
            'App\Listeners\HandlePasswordReset',
        ],
        'App\Events\LoanApplicationDeleted' => [
            'App\Listeners\HandleLoanApplicationDeleted',
        ],

        'App\Events\OnLoanPaymentMade' => [
            'App\Listeners\HandleLoanPaymentMade',
        ],

        'App\Events\GiveScore' => [
            'App\Listeners\HandleGiveScore',
        ],

        'App\Events\RequiredDocumentNotUploaded' => [
            'App\Listeners\HandleRequiredDocumentNotUploaded',
        ],

        'App\Events\RequiredDocumentUploaded' => [
            'App\Listeners\HandleRequiredDocumentUploaded',
        ],

        'App\Events\UserUpdatedProfile' => [
            'App\Listeners\HandleUserProfileUpdate',
        ],


    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
