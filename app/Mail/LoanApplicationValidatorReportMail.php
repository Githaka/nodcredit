<?php

namespace App\Mail;

use App\LoanApplication;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Application\ValidatorResult;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class LoanApplicationValidatorReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var LoanApplication
     */
    public $loanApplication;

    /**
     * @var ValidatorResult
     */
    public $validatorResult;

    /**
     * @var Collection
     */
    public $completedLoanApplications;

    /**
     * LoanApplicationValidatorReportMail constructor.
     * @param LoanApplication $loanApplication
     * @param ValidatorResult $validatorResult
     */
    public function __construct(LoanApplication $loanApplication, ValidatorResult $validatorResult)
    {
        $this->loanApplication = $loanApplication;
        $this->validatorResult = $validatorResult;
        $this->completedLoanApplications = $this->loanApplication->owner
            ->applications()
            ->where('id', '!=', $loanApplication->id)
            ->where('status', Application::STATUS_COMPLETED)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this
            ->subject('Loan Application Validator Report ' . $this->loanApplication->owner->name)
            ->view('emails.admin.loan-application-validator-report');

        foreach ($this->loanApplication->documents()->get() as $document) {
            if (file_exists($document->getFullpath())) {
                $mail->attach($document->getFullpath());
            }
        }

        return $mail;
    }
}
