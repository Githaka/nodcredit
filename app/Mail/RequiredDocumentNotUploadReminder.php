<?php

namespace App\Mail;

use App\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RequiredDocumentNotUploadReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public $sendRejected;

    public function __construct(LoanApplication $application, $sendRejected=false)
    {
        $this->application = $application;
        $this->sendRejected = $sendRejected;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->sendRejected ? 'Unfortunate! Loan Application Rejected' : '[Action Required] Your Loan Is Pending';
        return $this->view('emails.users.required-document-not-uploaded')
                    ->subject($subject);
    }
}
