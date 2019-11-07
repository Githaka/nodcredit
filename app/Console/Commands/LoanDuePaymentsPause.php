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

class LoanDuePaymentsPause extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:due-payments-pause';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan due Payments pause handler';

    /**
     * @var Template
     */
    private $increaseAndPauseMessage;

    /**
     * @var Template
     */
    private $chargeAndPauseMessage;


    public function __construct()
    {
        parent::__construct();

        try {
            $this->chargeAndPauseMessage = Template::findByKey('loan-due-payment-charge-and-pause');
            $this->increaseAndPauseMessage = Template::findByKey('loan-due-payment-increase-and-pause');
        }
        catch (\Exception $exception) {}
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $duePayments = PaymentCollection::findDueForOrMore(2, 1);

        $this->log('Loaded due payments: ' . $duePayments->count());

        /** @var Payment $payment */
        foreach ($duePayments->all() as $payment) {

            $pauseHandler = new LoanPause($payment->getApplication());

            $this->log("Loan [{$payment->getApplicationId()}]. Try to pause using all cards.");

            $paused = false;

            // Try to pause
            try {
                $paused = $pauseHandler->pauseUsingAllCards();
            }
            catch (\Exception $exception) {}

            if ($paused) {
                $this->sendChargeAndPauseMessage($payment);

                $this->log("Loan [{$payment->getApplicationId()}]. Successful charge and paused. Send message to user.");
            }
            // Pause failed.
            else {

                try {
                    $payment->increaseAmountBy(15);
                }
                catch (\Exception $exception) {
                    continue;
                }

                // Lets try to charge later
                $payment->setNeedToChargeForPause(true);

                $pauseHandler->shiftDueAtForMonth();

                $this->sendIncreaseAndPauseMessage($payment);

                $this->log("Loan [{$payment->getApplicationId()}]. Failed charge. Increase amount and pause. Send message to user.");
            }
        }
    }

    private function sendIncreaseAndPauseMessage(Payment $payment)
    {
        if (! $this->increaseAndPauseMessage) {
            return;
        }

        $content = $this->increaseAndPauseMessage->getMessage();
        $content = UserHandler::handle($content, $payment->getUser());
        $content = ReplaceHandler::handle($content, [
            '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2),
            '#LOAN_AMOUNT#' => 'NGN ' . number_format($payment->getApplication()->getAmountApproved(), 2),
        ]);

        $message = Message::create([
            'message' => $content,
            'message_type' => $this->increaseAndPauseMessage->getChannel(),
            'subject' => $this->increaseAndPauseMessage->getTitle(),
            'user_id' => $payment->getUser()->id,
            'sender' => 'system'
        ]);

        event(new SendMessage($message));
    }

    private function sendChargeAndPauseMessage(Payment $payment)
    {
        if (! $this->chargeAndPauseMessage) {
            return;
        }

        $content = $this->chargeAndPauseMessage->getMessage();
        $content = UserHandler::handle($content, $payment->getUser());
        $content = ReplaceHandler::handle($content, [
            '#PAYMENT_AMOUNT#' => 'NGN ' . number_format($payment->getAmount(), 2),
            '#LOAN_AMOUNT#' => 'NGN ' . number_format($payment->getApplication()->getAmountApproved(), 2),
        ]);

        $message = Message::create([
            'message' => $content,
            'message_type' => $this->chargeAndPauseMessage->getChannel(),
            'subject' => $this->chargeAndPauseMessage->getTitle(),
            'user_id' => $payment->getUser()->id,
            'sender' => 'system'
        ]);

        event(new SendMessage($message));
    }

    private function log(string $message, array $context = [])
    {
        Log::channel('loan-due-payments-pause')->info($message, $context);
    }
}
