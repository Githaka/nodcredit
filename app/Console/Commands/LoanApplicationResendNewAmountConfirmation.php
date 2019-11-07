<?php

namespace App\Console\Commands;

use App\LoanApplication;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Collections\ApplicationCollection;
use App\NodCredit\Message\MessageSender;
use App\NodCredit\Message\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanApplicationResendNewAmountConfirmation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:resend-new-amount-confirmation {--users=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend new amount confirmation';

    public function handle()
    {
        $dateLimit = now()->subHours(11);

        $now = now();

        $builder = LoanApplication::where('status', Application::STATUS_WAITING)
            ->where('amount_allowed', '>', 0)
            ->where('amount_allowed_at', '<', $dateLimit)
            ->whereRaw('MOD((TIMESTAMPDIFF(HOUR, `amount_allowed_at`, ?)) / 12, 1) = ?', [$now, 0])
        ;

        // Filter by users: "existing" or "all"
        if ($this->option('users') === 'existing') {
            $builder->whereIn('user_id', ApplicationCollection::getExistingUsersId());
        }

        $models = $builder->get();

        $this->log("Loaded loans: {$models->count()}");

        if (! $models->count()) {
            return true;
        }

        $messageTemplate = Template::findByKey('loan-application-confirm-new-amount');

        $applications = ApplicationCollection::makeCollectionFromModels($models);

        /** @var Application $application */
        foreach ($applications->all() as $application) {
            MessageSender::send($messageTemplate, $application->getAccountUser(), [
                '#LOAN_AMOUNT_CONFIRM_URL#' => route('account.loans.amount-confirm', ['id' => $application->getId()]),
                '#LOAN_AMOUNT_REJECT_URL#' => route('account.loans.amount-reject', ['id' => $application->getId()]),
                '#LOAN_AMOUNT_ALLOWED#' => 'N' . number_format($application->getAmountAllowed())
            ]);

            $this->log("[{$application->getId()}] Send new amount confirmation mail to customer");
        }

    }

    private function log(string $message, array $context = [])
    {
        Log::channel('loan-application-resend-new-amount-confirmation')->info($message, $context);
    }

}
