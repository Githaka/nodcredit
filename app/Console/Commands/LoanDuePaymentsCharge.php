<?php

namespace App\Console\Commands;

use App\Events\SendMessage;
use App\Message;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\LoanPause;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanDuePaymentsCharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments charge handler';

    /**
     * @var Template
     */
    private $failMessageTemplate;

    /**
     * @var Template
     */
    private $partiallySuccessMessageTemplate;


    public function __construct()
    {
        parent::__construct();

        $tempateKey = 'loan-due-payment-charge-failed';

        try {
            $this->failMessageTemplate = Template::findByKey($tempateKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$tempateKey}] not found ");
        }

        $partiallySuccessKey = 'loan-due-payment-charge-partially-success';

        try {
            $this->partiallySuccessMessageTemplate = Template::findByKey($partiallySuccessKey);
        }
        catch (\Exception $exception) {
            $this->log("Message Template [{$partiallySuccessKey}] not found ");
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sendFailedMessageAt = [6, 12, 19];

        $currentHour = date('G');

        $duePayments = PaymentCollection::findDueFor(1, 1);

        $this->log("Loaded due payments: {$duePayments->count()}");

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {

            $fullAmount = $payment->getAmount();

            // Try to charge 100%
            if ($this->chargeUsingPercent($payment, 100)) {
                continue;
            }

            // Try to charge 50%
            if ($this->chargeUsingPercent($payment, 50)) {

                // Send message
                $chargedAmount = floatval($fullAmount * 50 / 100);

                $this->sendPartiallySuccessMessage($payment, $chargedAmount);

                continue;
            }

            // Send Failed Message
            if (in_array($currentHour, $sendFailedMessageAt)) {
                $this->sendFailMessage($payment);
            }
        }
    }

    private function chargeUsingPercent(Payment $payment, int $percent): bool
    {
        $amount = floatval($payment->getAmount() * $percent / 100);

        // Try to charge
        $this->log("Payment [{$payment->getId()}]. Trying to charge {$amount} ({$percent}%) using all cards.");

        try {
            $charged = $payment->chargeUsingAllCards($amount);
        }
        catch (\Exception $exception) {
            $charged = false;
        }

        // Failed
        if (! $charged) {
            $this->log("Payment [{$payment->getId()}]. Failed charge {$amount} ({$percent}%)");

            return false;
        }

        // Successful
        $this->log("Payment [{$payment->getId()}]. Successful charge {$amount} ({$percent}%)");

        if ($payment->isPaid()) {
            $this->log("Payment [{$payment->getId()}]. Paid");
        }

        return true;
    }

    private function sendFailMessage(Payment $payment)
    {
        if (! $this->failMessageTemplate) {
            return;
        }

        $content = $this->failMessageTemplate->getMessage();
        $content = UserHandler::handle($content, $payment->getUser());
        $content = ReplaceHandler::handle($content, [
            '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2),
            '#LOAN_AMOUNT#' => 'NGN ' . number_format($payment->getApplication()->getAmountApproved(), 2),
        ]);

        $message = Message::create([
            'message' => $content,
            'message_type' => $this->failMessageTemplate->getChannel(),
            'subject' => $this->failMessageTemplate->getTitle(),
            'user_id' => $payment->getUser()->id,
            'sender' => 'system'
        ]);

        event(new SendMessage($message));

        $this->log("Payment [{$payment->getId()}]. Send 'fail message' [{$message->id}] to user");
    }

    /**
     * @param Payment $payment
     * @param float $chargedAmount
     */
    private function sendPartiallySuccessMessage(Payment $payment, float $chargedAmount)
    {
        if (! $this->partiallySuccessMessageTemplate) {
            return;
        }

        $content = $this->partiallySuccessMessageTemplate->getMessage();
        $content = UserHandler::handle($content, $payment->getUser());
        $content = ReplaceHandler::handle($content, [
            '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2),
            '#LOAN_AMOUNT#' => 'NGN ' . number_format($payment->getApplication()->getAmountApproved(), 2),
            '#CHARGED_AMOUNT#' => 'NGN ' . number_format($chargedAmount, 2),
        ]);

        $message = Message::create([
            'message' => $content,
            'message_type' => $this->partiallySuccessMessageTemplate->getChannel(),
            'subject' => $this->partiallySuccessMessageTemplate->getTitle(),
            'user_id' => $payment->getUser()->id,
            'sender' => 'system'
        ]);

        event(new SendMessage($message));

        $this->log("Payment [{$payment->getId()}]. Send 'partially success message' [{$message->id}] to user");
    }

    private function log(string $message, array $context = [])
    {
        Log::channel('loan-due-payments-charge')->info($message, $context);
    }

}
