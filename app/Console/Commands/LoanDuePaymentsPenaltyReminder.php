<?php

namespace App\Console\Commands;

use App\Mail\MessageSenderMail;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoanDuePaymentsPenaltyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-penalty-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments penalty reminder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var Settings $settings */
        $settings = app(Settings::class);

        $duePayments = PaymentCollection::findForPenalties();

        $now = now();

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {
            $mail = new MessageSenderMail('loan-due-payment-daily-penalty-reminder', $payment->getAccountUser(), [
                '#TODAY#' => $now->format('l'),
                '#PAYMENT_DUE_DAYS#' => $payment->getDueAt()->diffInDays($now),
                '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2),
                '#LOAN_REPAYMENT_URL#' => route('account.loans.repayment'),
                '#LOAN_DUE_PAYMENTS_PENALTY_PAUSE_DAYS#' => $settings->get('loan_due_payments_penalty_pause_days', 5),
                '#LOAN_DUE_PAYMENTS_PENALTY_PAUSE_THRESHOLD#' => $settings->get('loan_due_payments_penalty_pause_threshold', 20),
                '#AMOUNT_GROWTH_IMAGE_URL#' => route('amount-growth-graph', ['days' => 30, 'amount' => $payment->getAmount()])
            ]);

            $mail->from('recovery@nodcredit.com', 'NodCredit Recovery');

            Mail::to($payment->getUser()->email)->send($mail);
        }
    }

}
