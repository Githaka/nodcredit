<?php

namespace App\Console\Commands;

use App\Events\SendMessage;
use App\Message;
use App\NodCredit\Loan\Collections\PaymentCollection;
use App\NodCredit\Loan\Payment;
use App\NodCredit\Message\Template;
use App\NodCredit\Message\TemplateHandlers\ReplaceHandler;
use App\NodCredit\Message\TemplateHandlers\UserHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoanDuePaymentsTwice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-twice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments handler';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $duePayments = PaymentCollection::findDueFor(1, 2);

        $template = Template::findByKey('loan-due-payment-twice');

        $this->log("Loaded due payments: {$duePayments->count()}");

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {

            $content = $template->getMessage();
            $content = UserHandler::handle($content, $payment->getUser());
            $content = ReplaceHandler::handle($content, [
                '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2),
                '#LOAN_AMOUNT#' => 'NGN ' . number_format($payment->getApplication()->getAmountApproved(), 2),
            ]);

            $message = Message::create([
                'message' => $content,
                'message_type' => $template->getChannel(),
                'subject' => $template->getTitle(),
                'user_id' => $payment->getUser()->id,
                'sender' => 'system'
            ]);

            event(new SendMessage($message));

            $this->log("Payment [{$payment->getId()}]. Send message [{$message->id}] to user");
        }
    }

    private function log(string $message, array $context = [])
    {
        Log::channel('loan-due-payments-twice')->info($message, $context);
    }

}
