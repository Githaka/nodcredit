<?php

namespace App\Mail;

use App\LoanPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DefaulterCSVEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $filename  = LoanPayment::generateDefaultCSV();

        return $this->view('emails.admin.due-loan-alert')
                ->attach($filename)
                ->subject('Due Loan Records');
    }
}
