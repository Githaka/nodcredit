<?php

namespace App\Listeners;

use App\Mail\RequiredDocumentNotUploadReminder;
use App\NodCredit\Loan\Application;
use App\NodCredit\Message\MessageSender;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class HandleRequiredDocumentNotUploaded
{

    public function handle($event)
    {
        $application = new Application($event->application);
        $accountUser = $application->getAccountUser();

        if ($event->sendRejected) {
            $application->reject(false);

            MessageSender::send('loan-application-rejected', $accountUser);
        }
        else {
            MessageSender::send('loan-application-required-document-not-uploaded', $accountUser);
        }

    }
}
