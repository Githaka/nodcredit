<?php

namespace App\Console;

use App\FailedBilling;
use App\LoanPayment;
use App\Message;
use App\NodCredit\Settings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Events\SendMessage;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /** @var Settings $settings */
        $settings = app(Settings::class);

        $automationActiveSetting = (int) $settings->get('automation_active', 0);

        // Handle Applications
        if ($automationActiveSetting > 0) {

            // Handle Applications for "existing users". They must have one or more "completed loans".
            if ($automationActiveSetting === 1) {
                $handleUsers = 'existing';
            }
            // Handle Applications for "all users" (new + existing)
            else {
                $handleUsers = 'all';
            }

            $schedule->command("loan:handle-new-and-ready --users={$handleUsers}")->everyMinute();
            $schedule->command("loan:handle-processing-and-handled-by-parser --users={$handleUsers}")->everyMinute();
            $schedule->command("loan:handle-approval --users={$handleUsers}")->everyMinute();
            $schedule->command("loan:handle-old-and-ready --users={$handleUsers}")->everyThirtyMinutes();

            $schedule->command('loan:reject-new-and-handling-not-confirmed')->hourlyAt(10);
            $schedule->command('loan:reject-new-and-handling-confirmed')->hourlyAt(30);

            $schedule->command("loan:resend-new-amount-confirmation --users={$handleUsers}")->hourlyAt(40);

            // Unlock bank statements
            $schedule->command('loan:bank-statements-unlock')->everyMinute();

            // Application Automation: Document Parser
            $schedule->command('loan:bank-statements-parser-import')->everyMinute();
            $schedule->command('loan:bank-statements-parser-export')->everyMinute();
        }

        // Repayment reminders
        $schedule->command('loan:repayment-reminders')->dailyAt('14:00');

        // Penalty reminders
        $schedule->command('loan:due-payments-penalty-reminder')->weeklyOn(1, '10:00');
        $schedule->command('loan:due-payments-penalty-reminder')->weeklyOn(3, '10:00');
        $schedule->command('loan:due-payments-penalty-reminder')->weeklyOn(5, '10:00');

        // Due payments
        $schedule->command('loan:due-payments-due-counter')->dailyAt('00:00');
        $schedule->command('loan:due-payments-charge')->hourly()->runInBackground();
        $schedule->command('loan:due-payments-pause')->dailyAt('00:10')->runInBackground();
        $schedule->command('loan:due-payments-charge-for-pause')->hourly()->runInBackground();
        $schedule->command('loan:due-payments-twice')->dailyAt('08:00');
        $schedule->command('loan:due-payments-apply-penalty')->dailyAt('00:05');

        // Due payments: Low charger
        $schedule->command('loan:due-payments-decremental-low-charge')->hourly()->runInBackground();

        // Handler for New Messages, which did not send before
        $schedule->command('messages:handle-new')->everyFiveMinutes();

        // Geocode locations
        $schedule->command('user-location:geocode-new')->everyMinute()->runInBackground();

        // Serve user scores
        $schedule->command('user-scores:serve')->twiceDaily(1, 13);
        $schedule->command('user-scores:give-loan-past-due-date-scores')->dailyAt('00:10');

        // Investments
        $schedule->command('investments:complete-mature-investments')->everyFiveMinutes();
        $schedule->command('investments:profit-payments-payout-reminder')->hourlyAt(35);
        $schedule->command('investments:payout-profit-payments')->cron('2,22,42 * * * *')->runInBackground();
        $schedule->command('investments:payout-partial-liquidations')->cron('4,24,44 * * * *')->runInBackground();
        $schedule->command('investments:payout-full-liquidations')->cron('6,26,46 * * * *')->runInBackground();
        $schedule->command('investments:payout-completed-investments')->cron('8,29,48 * * * *')->runInBackground();

        // send due loans to support
        $schedule->call(function() {
            \Illuminate\Support\Facades\Mail::to('support@nodcredit.com')
                        ->bcc('admin@nodcredit.com')
                        ->send(new \App\Mail\DefaulterCSVEmail());
        })->timezone('Africa/Lagos')->dailyAt('06:30');

        /** send out reminder for document upload  6 HOURS Notice*/
        $schedule->call(function() {
            checkLoanRequiredDocumentUpload(6, false);
        })->timezone('Africa/Lagos')->hourly();

        /** send out reminder for document upload  24 HOURS Notice*/
        $schedule->call(function() {
            checkLoanRequiredDocumentUpload(24, true);
        })->timezone('Africa/Lagos')->hourly();


    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
