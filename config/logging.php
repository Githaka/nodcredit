<?php

use Monolog\Handler\StreamHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'loan-automation' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-automation.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-document-parsing' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-document-parsing.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-decremental-charge' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-decremental-charge.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-due-payments-charge' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-due-payments-charge.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-due-payments-low-charge' => [
            'driver' => 'daily',
            'path' => storage_path('logs/due-payments/loan-due-payments-low-charge.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-payment-charge' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-payment-charge.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-due-payments-charge-for-pause' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-due-payments-charge-for-pause.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-due-payments-pause' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-due-payments-pause.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-due-payments-due-counter' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-due-payments-due-counter.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-due-payments-apply-penalty' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-due-payments-apply-penalty.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-due-payments-twice' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-due-payments-twice.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-application-reject-new-and-handling-confirmed' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-application/reject-new-and-handling-confirmed.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-application-reject-new-and-handling-not-confirmed' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-application/reject-new-and-handling-not-confirmed.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-application-resend-new-amount-confirmation' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-application/resend-new-amount-confirmation.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'loan-documents-unlock-bank-statements' => [
            'driver' => 'daily',
            'path' => storage_path('logs/loan-documents/unlock-bank-statements.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'api-requests' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/requests.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'api-logs' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api/logs.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'investments-payouts' => [
            'driver' => 'daily',
            'path' => storage_path('logs/investments/payouts.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'investments-complete-mature-investments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/investments/complete-mature-investments.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'investments-payout-partial-liquidations' => [
            'driver' => 'daily',
            'path' => storage_path('logs/investments/payout-partial-liquidations.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'investments-payout-full-liquidations' => [
            'driver' => 'daily',
            'path' => storage_path('logs/investments/payout-full-liquidations.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'investments-payout-completed-investments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/investments/payout-completed-investments.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'investments-payout-profit-payments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/investments/payout-profit-payments.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'investments-profit-payments-payout-reminder' => [
            'driver' => 'single',
            'path' => storage_path('logs/investments/investments-profit-payments-payout-reminder.log'),
            'level' => 'debug',
        ],

        'paystack-requests' => [
            'driver' => 'daily',
            'path' => storage_path('logs/paystack/requests.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'user-scores-loan-past-due-date' => [
            'driver' => 'daily',
            'path' => storage_path('logs/user-scores/user-scores-loan-past-due-date.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'sling-requests' => [
            'driver' => 'daily',
            'path' => storage_path('logs/sling/requests.log'),
            'level' => 'debug',
            'days' => 0,
        ],

        'messages' => [
            'driver' => 'daily',
            'path' => storage_path('logs/messages/messages.log'),
            'level' => 'debug',
            'days' => 0,
        ]
    ],

];
