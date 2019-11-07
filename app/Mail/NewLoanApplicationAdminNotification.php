<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\LoanApplication;

class NewLoanApplicationAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $loan;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(LoanApplication $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->text('emails.admin.new-loan-application-notification')
                    ->subject('New loan application from ' . $this->loan->owner->name);
    }
}
