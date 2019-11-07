<?php

namespace App\Console\Commands;

use App\Events\SendMessage;
use App\Mail\LoanApplicationRejectedMail;
use App\Message;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Collections\ApplicationCollection;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoanApplicationRejectNewAndHandlingNotConfirmed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:reject-new-and-handling-not-confirmed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reject Loan Applications after wait period which are new and not confirmed for handling.';

    /**
     * @var Template
     */
    private $rejectMessage;

    /**
     * LoanApplicationRejectNewAndHandlingConfirmed constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $templateKey = 'loan-application-rejected';

        try {
            $this->rejectMessage = Template::findByKey($templateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$templateKey}] not found ");
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $applications = ApplicationCollection::findNewAndHandlingNotConfirmedMoreThan(4);

        $this->log("Loaded loans: {$applications->count()}");

        /** @var Application $application */
        foreach ($applications->all() as $application) {

            try {
                $rejected = $application->reject(false);
            }
            catch (\Exception $exception) {
                $this->log("[{$application->getId()}] Reject exception: {$exception->getMessage()}");
                continue;
            }

            if ($rejected) {
                $this->log("[{$application->getId()}] Rejected. Send mail to user.");

                $this->sendRejectMailToUser($application);
            }

        }
    }

    private function log(string $message, array $context = [])
    {
        Log::channel('loan-application-reject-new-and-handling-not-confirmed')->info($message, $context);
    }

    private function sendRejectMailToUser(Application $application)
    {
        if (! $this->rejectMessage) {
            return;
        }

        $content = $this->rejectMessage->getMessage();
        $content = UserHandler::handle($content, $application->getUser());

        $message = Message::create([
            'message' => $content,
            'message_type' => $this->rejectMessage->getChannel(),
            'subject' => $this->rejectMessage->getTitle(),
            'user_id' => $application->getUser()->id,
            'sender' => 'system'
        ]);

        event(new SendMessage($message));

    }

}
